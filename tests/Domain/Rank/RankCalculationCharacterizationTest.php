<?php

declare(strict_types=1);

namespace Tests\Domain\Rank;

use App\Domain\Rank\CalculatedPersonRank;
use App\Domain\Rank\PersonRankHistory;
use App\Domain\Rank\Rank;
use App\Domain\Rank\RankAchievement;
use App\Domain\Rank\RankCalculator;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function array_map;

final class RankCalculationCharacterizationTest extends TestCase
{
    #[Test]
    public function it_returns_without_rank_when_there_are_no_qualifying_achievements(): void
    {
        $result = $this->calculate([]);

        $this->assertSame(Rank::WithoutRank, $result->currentRank);
        $this->assertSame([], $result->history);
    }

    #[Test]
    public function it_activates_ordinary_ranks_on_the_event_date(): void
    {
        $result = $this->calculate([$this->achievement(Rank::FirstRank, '2026-01-10')]);

        $this->assertSame(Rank::FirstRank, $result->currentRank);
        $this->assertSame('2026-01-10', $result->activatedOn?->toDateString());
        $this->assertSame('2028-01-10', $result->finishedOn?->toDateString());
        $this->assertSame('completion', $result->history[0]->changeType);
    }

    #[Test]
    public function it_keeps_confirmation_required_rank_in_history_until_manual_activation(): void
    {
        $result = $this->calculate([$this->achievement(Rank::MasterOfSport, '2026-01-10')]);

        $this->assertSame(Rank::WithoutRank, $result->currentRank);
        $this->assertNotInstanceOf(CarbonImmutable::class, $result->history[0]->activatedOn);
        $this->assertSame('completion', $result->history[0]->changeType);
    }

    #[Test]
    public function it_uses_the_manual_activation_date_as_the_start_of_rank_period(): void
    {
        $result = $this->calculate([
            $this->achievement(Rank::CandidateMaster, '2026-01-10', activatedOn: '2026-02-01'),
        ]);

        $this->assertSame(Rank::CandidateMaster, $result->currentRank);
        $this->assertSame('2026-02-01', $result->startedOn?->toDateString());
        $this->assertSame('2028-02-01', $result->finishedOn?->toDateString());
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
            static fn (PersonRankHistory $history): string => $history->changeType,
            $result->history,
        ));
        $this->assertSame(Rank::SecondRank, $result->currentRank);
    }

    #[Test]
    public function it_records_a_lower_following_achievement_as_downgrade(): void
    {
        $result = $this->calculate([
            $this->achievement(Rank::FirstRank, '2025-01-10', 1),
            $this->achievement(Rank::SecondRank, '2026-01-10', 2),
        ]);

        $this->assertSame('downgrade', $result->history[1]->changeType);
    }

    #[Test]
    public function it_ignores_mass_and_out_of_competition_achievements(): void
    {
        $result = $this->calculate([
            $this->achievement(Rank::FirstRank, '2026-01-10', massCompetition: true),
            $this->achievement(Rank::SecondRank, '2026-01-11', outOfCompetition: true),
        ]);

        $this->assertSame(Rank::WithoutRank, $result->currentRank);
        $this->assertSame([], $result->history);
    }

    #[Test]
    public function it_enforces_the_junior_age_limit_and_awards_junior_third_rank_after_three_results(): void
    {
        $adult = $this->calculate([
            $this->achievement(Rank::JuniorFirstRank, '2026-01-10', birthday: '2000-01-01'),
        ]);
        $junior = $this->calculate([
            $this->achievement(Rank::WithoutRank, '2026-01-10', 1, birthday: '2010-01-01'),
            $this->achievement(Rank::WithoutRank, '2026-02-10', 2, birthday: '2010-01-01'),
            $this->achievement(Rank::WithoutRank, '2026-03-10', 3, birthday: '2010-01-01'),
        ]);

        $this->assertSame(Rank::WithoutRank, $adult->currentRank);
        $this->assertSame(Rank::JuniorThirdRank, $junior->currentRank);
        $this->assertSame('junior_third', $junior->history[0]->changeType);
    }

    #[Test]
    public function it_expires_rank_after_two_years_without_a_new_achievement(): void
    {
        $result = new RankCalculator()->calculate([
            $this->achievement(Rank::FirstRank, '2024-01-10'),
        ], CarbonImmutable::parse('2026-01-11'));

        $this->assertSame(Rank::WithoutRank, $result->currentRank);
    }

    /** @param list<RankAchievement> $achievements */
    private function calculate(array $achievements): CalculatedPersonRank
    {
        return new RankCalculator()->calculate($achievements, CarbonImmutable::parse('2026-07-01'));
    }

    private function achievement(
        Rank $rank,
        string $achievedOn,
        int $protocolLineId = 1,
        bool $massCompetition = false,
        bool $outOfCompetition = false,
        ?string $birthday = null,
        ?string $activatedOn = null,
    ): RankAchievement {
        return new RankAchievement(
            personId: 1,
            protocolLineId: $protocolLineId,
            distanceId: $protocolLineId + 10,
            eventId: $protocolLineId + 20,
            competitionId: $protocolLineId + 30,
            rank: $rank,
            achievedOn: CarbonImmutable::parse($achievedOn),
            activatedOn: $activatedOn === null ? null : CarbonImmutable::parse($activatedOn),
            massCompetition: $massCompetition,
            outOfCompetition: $outOfCompetition,
            birthday: $birthday === null ? null : CarbonImmutable::parse($birthday),
        );
    }
}
