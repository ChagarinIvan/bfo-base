<?php

declare(strict_types=1);

namespace Tests\Feature\Rank;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\RebuildExpiredPersonRanks;
use App\Application\Service\Person\RebuildExpiredPersonRanksService;
use App\Domain\Person\Person;
use App\Domain\Rank\Rank;
use App\Domain\Shared\Clock;
use App\Domain\Shared\FrozenClock;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RebuildExpiredPersonRanksTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_rebuilds_only_people_with_an_expired_current_rank(): void
    {
        /** @var Person $expired */
        $expired = Person::factory()->createOne([
            'id' => 1,
            'current_rank' => Rank::FirstRank,
            'current_rank_finished_on' => Carbon::today()->subDay(),
        ]);
        /** @var Person $actual */
        $actual = Person::factory()->createOne([
            'id' => 2,
            'current_rank' => Rank::SecondRank,
            'current_rank_finished_on' => Carbon::today()->addDay(),
        ]);

        app(RebuildExpiredPersonRanksService::class)->execute(new RebuildExpiredPersonRanks(
            userId: new UserId(1),
            criteria: ['rankFinishedBefore' => Carbon::today()],
        ));

        $expired->refresh();
        $actual->refresh();
        $this->assertSame('daily', $expired->rank_calculation_reason?->value);
        $this->assertNotNull($expired->rank_calculated_at);
        $this->assertNull($actual->rank_calculated_at);
        $this->assertSame(Rank::WithoutRank, $expired->current_rank);
        $this->assertSame(Rank::SecondRank, $actual->current_rank);
    }
    #[Test]
    public function the_daily_command_records_its_source_on_the_selected_person(): void
    {
        $person = Person::factory()->createOne([
            'current_rank' => Rank::FirstRank,
            'current_rank_finished_on' => '2026-10-31',
        ]);
        $this->app->instance(Clock::class, new FrozenClock(Carbon::parse('2026-11-01 00:10:00')));
        $this->artisan('persons:ranks:rebuild-expired 1')->assertSuccessful();

        $this->assertSame('daily', $person->fresh()->rank_calculation_reason?->value);
        $this->assertSame('2026-11-01 00:10:00', $person->fresh()->rank_calculated_at?->toDateTimeString());
    }
}
