<?php

declare(strict_types=1);

namespace Tests\Domain\Person;

use App\Domain\Person\RankActivationPolicy;
use App\Domain\Person\RankFact;
use App\Domain\Rank\Rank;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function array_filter;
use function array_first;

final class RankActivationPolicyTest extends TestCase
{
    #[Test]
    public function every_confirmation_required_rank_accepts_prior_equal_or_stronger_confirmation(): void
    {
        $ranks = [Rank::FirstRank, Rank::CandidateMaster, Rank::MasterOfSport, Rank::WorldClassMaster];
        foreach ($ranks as $rank) {
            foreach ($ranks as $prior) {
                $result = new RankActivationPolicy()->apply([
                    $this->fact(2, $rank, '2026-01-01'),
                    $this->fact(1, $prior, '2020-01-01', '2020-02-01'),
                ]);
                $target = array_first(array_filter($result, static fn (RankFact $fact): bool => $fact->protocolLineId === 2));
                if ($prior->value >= $rank->value) {
                    $this->assertSame('2026-01-01', $target->activatedOn?->toDateString());
                } else {
                    $this->assertNull($target->activatedOn);
                }
            }
        }
    }

    #[Test]
    public function later_or_unconfirmed_higher_rank_cannot_confirm_an_earlier_achievement(): void
    {
        foreach ([null, '2026-02-01'] as $activation) {
            $result = new RankActivationPolicy()->apply([
                $this->fact(1, Rank::MasterOfSport, '2025-01-01', $activation),
                $this->fact(2, Rank::CandidateMaster, '2026-01-01'),
            ]);
            $target = array_first(array_filter($result, static fn (RankFact $fact): bool => $fact->protocolLineId === 2));
            $this->assertNull($target->activatedOn);
        }
    }

    #[Test]
    public function first_time_achievement_waits_and_manual_date_has_priority(): void
    {
        $pending = new RankActivationPolicy()->apply([$this->fact(1, Rank::CandidateMaster, '2026-01-01')]);
        $this->assertNull($pending[0]->activatedOn);
        $manual = new RankActivationPolicy()->apply([
            $this->fact(1, Rank::MasterOfSport, '2025-01-01', '2025-02-01'),
            $this->fact(2, Rank::CandidateMaster, '2026-01-01', '2026-03-01'),
        ]);
        $target = array_first(array_filter($manual, static fn (RankFact $fact): bool => $fact->protocolLineId === 2));
        $this->assertSame('2026-03-01', $target->activatedOn?->toDateString());
    }

    #[Test]
    public function lower_ranks_keep_their_separate_delayed_activation_rule(): void
    {
        $result = new RankActivationPolicy()->apply([
            $this->fact(1, Rank::FirstRank, '2025-01-01', '2025-02-01'),
            new RankFact(2, 2, 2, 2, Rank::SecondRank, Carbon::parse('2026-01-01'), null, true),
        ]);
        $target = array_first(array_filter($result, static fn (RankFact $fact): bool => $fact->protocolLineId === 2));
        $this->assertNull($target->activatedOn);
        $this->assertTrue($target->delayedActivation);
    }

    private function fact(int $id, Rank $rank, string $achieved, ?string $activated = null): RankFact
    {
        return new RankFact($id, $id, $id, $id, $rank, Carbon::parse($achieved), $activated === null ? null : Carbon::parse($activated));
    }
}
