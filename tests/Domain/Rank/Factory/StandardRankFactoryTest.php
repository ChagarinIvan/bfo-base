<?php

declare(strict_types=1);

namespace Tests\Domain\Rank\Factory;

use App\Domain\Rank\Factory\RankInput;
use App\Domain\Rank\Factory\StandardRankFactory;
use App\Domain\Rank\Rank;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StandardRankFactoryTest extends TestCase
{
    private StandardRankFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new StandardRankFactory();
    }

    #[Test]
    public function it_maps_all_input_fields_to_rank(): void
    {
        $start = Carbon::createFromFormat('Y-m-d', '2023-05-01');
        $activated = Carbon::createFromFormat('Y-m-d', '2023-06-01');
        $finish = Carbon::createFromFormat('Y-m-d', '2024-05-01');

        $rank = $this->factory->create(new RankInput(
            personId: 42,
            eventId: 7,
            rank: Rank::JUNIOR_THIRD_RANK,
            startDate: $start,
            activatedDate: $activated,
            finishDate: $finish,
        ));

        $this->assertInstanceOf(Rank::class, $rank);
        $this->assertSame(42, $rank->person_id);
        $this->assertSame(7, $rank->event_id);
        $this->assertSame(Rank::JUNIOR_THIRD_RANK, $rank->rank);
        $this->assertSame('2023-05-01', $rank->start_date->toDateString());
        $this->assertSame('2023-06-01', $rank->activated_date->toDateString());
        $this->assertSame('2024-05-01', $rank->finish_date->toDateString());
    }

    #[Test]
    public function it_defaults_finish_date_to_two_years_after_start_when_not_provided(): void
    {
        $start = Carbon::createFromFormat('Y-m-d', '2023-05-01');

        $rank = $this->factory->create(new RankInput(
            personId: 1,
            eventId: null,
            rank: Rank::THIRD_RANK,
            startDate: $start,
            activatedDate: null,
        ));

        $this->assertSame('2025-05-01', $rank->finish_date->toDateString());
        $this->assertNotInstanceOf(Carbon::class, $rank->activated_date);
        $this->assertNull($rank->event_id);
        // исходный startDate не мутируется (фабрика клонирует)
        $this->assertSame('2023-05-01', $start->toDateString());
    }
}
