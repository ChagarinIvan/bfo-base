<?php

declare(strict_types=1);

namespace Tests\Domain\Person;

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

final class RankCalculatorTest extends TestCase
{
    #[Test]
    public function it_projects_current_rank_and_preserves_protocol_history(): void
    {
        $calculator = new RankCalculator();

        $result = $calculator->calculate([
            new RankFact(10, 20, 30, 40, Rank::FirstRank, Carbon::parse('2026-01-01'), null),
            new RankFact(11, 21, 31, 41, Rank::FirstRank, Carbon::parse('2026-03-01'), null),
        ], new Person(), Carbon::parse('2026-07-01'));

        $this->assertSame(Rank::FirstRank, $result->current->rank);
        $this->assertCount(2, $result->history);
        $this->assertSame(RankChangeType::Extension, $result->history[1]->change_type);
        $this->assertSame('2028-03-01', $result->history[0]->finished_on?->format('Y-m-d'));
        $this->assertSame('2028-03-01', $result->history[1]->finished_on?->format('Y-m-d'));
    }

    #[Test]
    public function it_does_not_activate_a_rank_that_requires_confirmation(): void
    {
        $result = new RankCalculator()->calculate([
            new RankFact(10, 20, 30, 40, Rank::MasterOfSport, Carbon::parse('2026-01-01'), null),
        ], new Person(), Carbon::parse('2026-07-01'));

        $this->assertSame(Rank::WithoutRank, $result->current->rank);
        $this->assertNotInstanceOf(Carbon::class, $result->history[0]->activated_on);
    }

    #[Test]
    public function it_awards_junior_third_rank_after_three_qualifying_starts(): void
    {
        $birthday = Carbon::parse('2010-05-01');
        $starts = [];
        foreach ([1, 2, 3] as $index => $day) {
            $starts[] = new RankFact(
                20 + $index,
                30 + $index,
                40 + $index,
                50 + $index,
                Rank::WithoutRank,
                Carbon::create(2026, 2, $day),
                null,
            );
        }

        $person = new Person();
        $person->birthday = $birthday;
        $result = new RankCalculator()->calculate($starts, $person, Carbon::parse('2026-07-01'));

        $this->assertSame(Rank::JuniorThirdRank, $result->current->rank);
        $this->assertSame(RankChangeType::Completion, $result->history[0]->change_type);
    }

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
        $this->assertSame(RankChangeType::Completion, $result->history[0]->change_type);
    }

    #[Test]
    public function it_keeps_confirmation_required_rank_in_history_until_manual_activation(): void
    {
        $result = $this->calculate([$this->achievement(Rank::MasterOfSport, '2026-01-10')]);

        $this->assertSame(Rank::WithoutRank, $result->current->rank);
        $this->assertNotInstanceOf(Carbon::class, $result->history[0]->activated_on);
        $this->assertSame(RankChangeType::Completion, $result->history[0]->change_type);
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
            static fn (PersonRankHistory $history): string => $history->change_type->value,
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

        $this->assertSame(RankChangeType::Downgrade, $result->history[1]->change_type);
    }

    #[Test]
    public function it_enforces_the_junior_age_limit_and_awards_junior_third_rank_after_three_results(): void
    {
        $adult = $this->calculate([$this->achievement(Rank::JuniorFirstRank, '2026-01-10')], '2000-01-01');
        $junior = $this->calculate([
            $this->achievement(Rank::WithoutRank, '2026-01-10', 1),
            $this->achievement(Rank::WithoutRank, '2026-02-10', 2),
            $this->achievement(Rank::WithoutRank, '2026-03-10', 3),
        ], '2010-01-01');

        $this->assertSame(Rank::WithoutRank, $adult->current->rank);
        $this->assertSame(Rank::JuniorThirdRank, $junior->current->rank);
        $this->assertSame(RankChangeType::Completion, $junior->history[0]->change_type);
    }

    #[Test]
    public function it_expires_rank_after_two_years_without_a_new_achievement(): void
    {
        $result = $this->calculate([$this->achievement(Rank::FirstRank, '2024-01-10')]);

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
