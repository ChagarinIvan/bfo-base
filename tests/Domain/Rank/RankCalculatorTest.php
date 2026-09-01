<?php

declare(strict_types=1);

namespace Tests\Domain\Rank;

use App\Domain\Rank\Rank;
use App\Domain\Rank\RankAchievement;
use App\Domain\Rank\RankCalculator;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RankCalculatorTest extends TestCase
{
    #[Test]
    public function it_projects_current_rank_and_preserves_protocol_history(): void
    {
        $calculator = new RankCalculator();

        $result = $calculator->calculate([
            new RankAchievement(1, 10, 20, 30, 40, Rank::FirstRank, CarbonImmutable::parse('2026-01-01'), null),
            new RankAchievement(1, 11, 21, 31, 41, Rank::FirstRank, CarbonImmutable::parse('2026-03-01'), null),
        ], CarbonImmutable::parse('2026-07-01'));

        $this->assertSame(Rank::FirstRank, $result->currentRank);
        $this->assertCount(2, $result->history);
        $this->assertSame('extension', $result->history[1]->changeType);
    }

    #[Test]
    public function it_does_not_activate_a_rank_that_requires_confirmation(): void
    {
        $result = new RankCalculator()->calculate([
            new RankAchievement(1, 10, 20, 30, 40, Rank::MasterOfSport, CarbonImmutable::parse('2026-01-01'), null),
        ], CarbonImmutable::parse('2026-07-01'));

        $this->assertSame(Rank::WithoutRank, $result->currentRank);
        $this->assertNotInstanceOf(CarbonImmutable::class, $result->history[0]->activatedOn);
    }

    #[Test]
    public function it_awards_junior_third_rank_after_three_qualifying_starts(): void
    {
        $birthday = CarbonImmutable::parse('2010-05-01');
        $starts = [];
        foreach ([1, 2, 3] as $index => $day) {
            $starts[] = new RankAchievement(
                1,
                20 + $index,
                30 + $index,
                40 + $index,
                50 + $index,
                Rank::WithoutRank,
                CarbonImmutable::create(2026, 2, $day),
                null,
                birthday: $birthday,
            );
        }

        $result = new RankCalculator()->calculate($starts, CarbonImmutable::parse('2026-07-01'));

        $this->assertSame(Rank::JuniorThirdRank, $result->currentRank);
        $this->assertSame('junior_third', $result->history[0]->changeType);
    }
}
