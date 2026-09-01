<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Console\Commands;

use App\Bridge\Laravel\Console\Commands\RefillPersonRanksCommand;
use App\Domain\Person\Person;
use App\Domain\Rank\Rank;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RefillPersonRanksCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @see RefillPersonRanksCommand */
    #[Test]
    public function it_rebuilds_all_active_people_from_protocol_lines(): void
    {
        /** @var Person $person */
        $person = Person::factory()->createOne([
            'id' => 1,
            'current_rank' => Rank::FirstRank,
            'current_rank_started_on' => '2026-01-01',
            'current_rank_activated_on' => '2026-01-01',
            'current_rank_finished_on' => '2028-01-01',
        ]);

        $this->artisan('ranks:refill')->assertSuccessful();

        $person->refresh();
        $this->assertSame(Rank::WithoutRank, $person->current_rank);
        $this->assertNotInstanceOf(Carbon::class, $person->current_rank_started_on);
        $this->assertNotInstanceOf(Carbon::class, $person->current_rank_activated_on);
        $this->assertNotInstanceOf(Carbon::class, $person->current_rank_finished_on);
    }
}
