<?php

declare(strict_types=1);

namespace Tests\Domain\Rank;

use App\Domain\Person\Person;
use App\Domain\Person\RankCalculator;
use App\Domain\Person\RankChangeType;
use App\Domain\Person\RankFact;
use App\Domain\Rank\Rank;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

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
        $this->assertSame(RankChangeType::Extension, $result->history[1]->changeType);
        $this->assertSame('2028-03-01', $result->history[0]->finishedOn?->format('Y-m-d'));
        $this->assertSame('2028-03-01', $result->history[1]->finishedOn?->format('Y-m-d'));
    }

    #[Test]
    public function it_does_not_activate_a_rank_that_requires_confirmation(): void
    {
        $result = new RankCalculator()->calculate([
            new RankFact(10, 20, 30, 40, Rank::MasterOfSport, Carbon::parse('2026-01-01'), null),
        ], new Person(), Carbon::parse('2026-07-01'));

        $this->assertSame(Rank::WithoutRank, $result->current->rank);
        $this->assertNotInstanceOf(Carbon::class, $result->history[0]->activatedOn);
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
        $this->assertSame(RankChangeType::Completion, $result->history[0]->changeType);
    }
}
