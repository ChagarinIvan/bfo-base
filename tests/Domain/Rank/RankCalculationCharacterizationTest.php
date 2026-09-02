<?php

declare(strict_types=1);

namespace Tests\Domain\Rank;

use App\Domain\Person\Person;
use App\Domain\Person\PersonRankHistory;
use App\Domain\Person\PersonRankState;
use App\Domain\Person\RankCalculator;
use App\Domain\Person\RankChangeType;
use App\Domain\Person\RankFact;
use App\Domain\Rank\Rank;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function array_map;

final class RankCalculationCharacterizationTest extends TestCase
{
    #[Test]
    public function it_returns_without_rank_when_there_are_no_qualifying_achievements(): void
    {
        $result = $this->calculate([]);

        $this->assertSame(Rank::WithoutRank, $result->current->rank);
        $this->assertSame([], $result->history);
    }

    #[Test]
    public function it_activates_ordinary_ranks_on_the_event_date(): void
    {
        $result = $this->calculate([$this->achievement(Rank::FirstRank, '2026-01-10')]);

        $this->assertSame(Rank::FirstRank, $result->current->rank);
        $this->assertSame('2026-01-10', $result->current->activatedOn?->format('Y-m-d'));
        $this->assertSame('2028-01-10', $result->current->finishedOn?->format('Y-m-d'));
        $this->assertSame(RankChangeType::Completion, $result->history[0]->changeType);
    }

    #[Test]
    public function it_keeps_confirmation_required_rank_in_history_until_manual_activation(): void
    {
        $result = $this->calculate([$this->achievement(Rank::MasterOfSport, '2026-01-10')]);

        $this->assertSame(Rank::WithoutRank, $result->current->rank);
        $this->assertNotInstanceOf(Carbon::class, $result->history[0]->activatedOn);
        $this->assertSame(RankChangeType::Completion, $result->history[0]->changeType);
    }

    #[Test]
    public function it_uses_the_manual_activation_date_as_the_start_of_rank_period(): void
    {
        $result = $this->calculate([
            $this->achievement(Rank::CandidateMaster, '2026-01-10', activatedOn: '2026-02-01'),
        ]);

        $this->assertSame(Rank::CandidateMaster, $result->current->rank);
        $this->assertSame('2026-02-01', $result->current->startedOn?->format('Y-m-d'));
        $this->assertSame('2028-02-01', $result->current->finishedOn?->format('Y-m-d'));
    }

    #[Test]
    public function it_extends_and_promotes_completed_ranks(): void
    {
        $result = $this->calculate([
            $this->achievement(Rank::ThirdRank, '2025-01-10', 1),
            $this->achievement(Rank::ThirdRank, '2026-01-10', 2),
            $this->achievement(Rank::SecondRank, '2026-02-10', 3),
        ]);

        $this->assertSame(['completion', 'extension', 'promotion'], array_map(
            static fn (PersonRankHistory $history): string => $history->changeType->value,
            $result->history,
        ));
        $this->assertSame(Rank::SecondRank, $result->current->rank);
    }

    #[Test]
    public function it_records_a_lower_following_achievement_as_downgrade(): void
    {
        $result = $this->calculate([
            $this->achievement(Rank::FirstRank, '2025-01-10', 1),
            $this->achievement(Rank::SecondRank, '2026-01-10', 2),
        ]);

        $this->assertSame(RankChangeType::Downgrade, $result->history[1]->changeType);
    }

    #[Test]
    public function it_enforces_the_junior_age_limit_and_awards_junior_third_rank_after_three_results(): void
    {
        $adult = $this->calculate([
            $this->achievement(Rank::JuniorFirstRank, '2026-01-10'),
        ], '2000-01-01');
        $junior = $this->calculate([
            $this->achievement(Rank::WithoutRank, '2026-01-10', 1),
            $this->achievement(Rank::WithoutRank, '2026-02-10', 2),
            $this->achievement(Rank::WithoutRank, '2026-03-10', 3),
        ], '2010-01-01');

        $this->assertSame(Rank::WithoutRank, $adult->current->rank);
        $this->assertSame(Rank::JuniorThirdRank, $junior->current->rank);
        $this->assertSame(RankChangeType::Completion, $junior->history[0]->changeType);
    }

    #[Test]
    public function it_expires_rank_after_two_years_without_a_new_achievement(): void
    {
        $result = new RankCalculator()->calculate([
            $this->achievement(Rank::FirstRank, '2024-01-10'),
        ], $this->person(null), Carbon::parse('2026-01-11'));

        $this->assertSame(Rank::WithoutRank, $result->current->rank);
    }

    /** @param list<RankFact> $achievements */
    private function calculate(array $achievements, ?string $birthday = null): PersonRankState
    {
        return new RankCalculator()->calculate(
            $achievements,
            $this->person($birthday),
            Carbon::parse('2026-07-01'),
        );
    }

    private function achievement(
        Rank $rank,
        string $achievedOn,
        int $protocolLineId = 1,
        ?string $activatedOn = null,
    ): RankFact {
        return new RankFact(
            protocolLineId: $protocolLineId,
            distanceId: $protocolLineId + 10,
            eventId: $protocolLineId + 20,
            competitionId: $protocolLineId + 30,
            rank: $rank,
            achievedOn: Carbon::parse($achievedOn),
            activatedOn: $activatedOn === null ? null : Carbon::parse($activatedOn),
        );
    }

    private function person(?string $birthday): Person
    {
        $person = new Person();
        $person->birthday = $birthday === null ? null : Carbon::parse($birthday);

        return $person;
    }
}
