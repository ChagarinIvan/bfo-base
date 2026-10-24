<?php

declare(strict_types=1);

namespace Tests\Domain\Person;

use App\Domain\Person\PersonRankHistory;
use App\Domain\Person\RankCalculator;
use App\Domain\Person\RankFact;
use App\Domain\Rank\Rank;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function array_filter;
use function array_first;

final class RankCalculationRegressionTest extends TestCase
{
    #[Test]
    public function future_confirmation_schedules_rebuild_without_early_extension(): void
    {
        $result = new RankCalculator()->calculateFor([
            $this->fact(1, Rank::CandidateMaster, '2025-01-01', '2025-01-01'),
            $this->fact(2, Rank::CandidateMaster, '2026-01-01', '2026-12-01'),
        ], 1, null, Carbon::parse('2026-07-01'));

        $this->assertSame('2026-12-01', $result->nextActivationOn?->toDateString());
        $this->assertSame('2027-01-01', $result->current->finishedOn?->toDateString());
    }

    #[Test]
    public function promotion_closes_lower_period_in_activation_order(): void
    {
        $result = new RankCalculator()->calculateFor([
            $this->fact(1, Rank::CandidateMaster, '2024-01-01', '2026-01-01'),
            $this->fact(2, Rank::FirstRank, '2024-02-01', '2024-02-01'),
        ], 1, null, Carbon::parse('2026-07-01'));

        $lower = array_first(array_filter($result->history, static fn (PersonRankHistory $entry): bool => $entry->rank === Rank::FirstRank));
        $this->assertSame('2026-01-01', $lower->finished_on->toDateString());
    }

    #[Test]
    public function last_day_of_validity_includes_the_whole_calendar_day(): void
    {
        $result = new RankCalculator()->calculateFor([
            $this->fact(1, Rank::CandidateMaster, '2024-07-01', '2024-07-01'),
        ], 1, null, Carbon::parse('2026-07-01 18:00:00'));
        $this->assertSame(Rank::CandidateMaster, $result->current->rank);
    }

    #[Test]
    public function repeat_first_rank_uses_prior_equal_or_stronger_confirmation_even_after_expiry(): void
    {
        foreach ([Rank::FirstRank, Rank::CandidateMaster, Rank::MasterOfSport, Rank::WorldClassMaster] as $prior) {
            $first = new RankFact(2, 2, 2, 2, Rank::FirstRank, Carbon::parse('2026-01-01'), null, false);
            $result = new RankCalculator()->calculateFor([
                $first, $this->fact(1, $prior, '2020-01-01', '2020-02-01'),
            ], 1, null, Carbon::parse('2026-07-01'));
            $entry = array_first(array_filter($result->history, static fn (PersonRankHistory $row): bool => $row->protocol_line_id === 2));
            $this->assertSame('2026-01-01', $entry->activated_on?->toDateString());
        }
    }

    #[Test]
    public function pending_or_later_confirmation_does_not_qualify_and_manual_date_wins(): void
    {
        foreach ([null, '2026-02-01'] as $confirmation) {
            $result = new RankCalculator()->calculateFor([
                new RankFact(1, 1, 1, 1, Rank::CandidateMaster, Carbon::parse('2025-01-01'), $confirmation === null ? null : Carbon::parse($confirmation)),
                new RankFact(2, 2, 2, 2, Rank::FirstRank, Carbon::parse('2026-01-01'), null, false),
            ], 1, null, Carbon::parse('2026-07-01'));
            $entry = array_first(array_filter($result->history, static fn (PersonRankHistory $row): bool => $row->protocol_line_id === 2));
            $this->assertNull($entry->activated_on);
        }
    }

    private function fact(int $id, Rank $rank, string $achieved, string $activated): RankFact
    {
        return new RankFact($id, $id, $id, $id, $rank, Carbon::parse($achieved), Carbon::parse($activated));
    }
}
