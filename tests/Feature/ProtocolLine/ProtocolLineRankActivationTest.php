<?php

declare(strict_types=1);

namespace Tests\Feature\ProtocolLine;

use App\Domain\Auth\Impression;
use App\Domain\Event\Event;
use App\Domain\ProtocolLine\Exception\UnableToActivateRank;
use App\Domain\ProtocolLine\ProtocolLine;
use Carbon\Carbon;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ProtocolLineRankActivationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_rejects_an_early_date_without_mutating_state_or_recording_an_event(): void
    {
        $this->seed(ProtocolLinesSeeder::class);
        Event::query()->whereKey(102)->update(['date' => '2026-01-10']);
        /** @var ProtocolLine $line */
        $line = ProtocolLine::factory()->createOne([
            'distance_id' => 104,
            'activate_rank' => '2026-01-20',
        ]);

        try {
            $line->activateRank(Carbon::parse('2026-01-09'), new Impression(Carbon::parse('2026-01-25'), 1));
            $this->fail('An activation before the achievement must be rejected.');
        } catch (UnableToActivateRank) {
            $this->assertSame('2026-01-20', $line->activate_rank?->toDateString());
            $this->assertSame([], $line->releasedEvents());
        }
    }
}
