<?php

declare(strict_types=1);

namespace Tests\Feature\ProtocolLine;

use App\Domain\Competition\Competition;
use App\Domain\Event\Event;
use App\Services\ProtocolLineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ProtocolLineServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_accepts_a_missing_athlete_rank_from_a_protocol(): void
    {
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne();
        /** @var Event $event */
        $event = Event::factory()->createOne(['competition_id' => $competition->id]);

        $lines = app(ProtocolLineService::class)->fillProtocolLines($event->id, new Collection([[
            'serial_number' => 1,
            'lastname' => 'Runner',
            'firstname' => 'Test',
            'club' => 'Club',
            'year' => 2000,
            'rank' => null,
            'runner_number' => 1,
            'time' => null,
            'place' => null,
            'complete_rank' => null,
            'points' => null,
            'vk' => false,
            'group' => 'M21',
            'distance' => ['length' => 1000, 'points' => 10],
        ]]));

        $this->assertSame('', $lines->sole()->rank);
    }
}
