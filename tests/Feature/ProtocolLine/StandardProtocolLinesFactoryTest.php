<?php

declare(strict_types=1);

namespace Tests\Feature\ProtocolLine;

use App\Domain\Competition\Competition;
use App\Domain\Event\Event;
use App\Domain\ProtocolLine\Factory\StandardProtocolLinesFactory;
use App\Domain\ProtocolLine\ProtocolLineInput;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StandardProtocolLinesFactoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_accepts_a_missing_athlete_rank_from_a_protocol(): void
    {
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne();
        /** @var Event $event */
        $event = Event::factory()->createOne(['competition_id' => $competition->id]);
        $lines = app(StandardProtocolLinesFactory::class)->create($event, [new ProtocolLineInput(
            serialNumber: 1,
            lastname: 'Runner',
            firstname: 'Test',
            club: 'Club',
            year: 2000,
            rank: null,
            runnerNumber: 1,
            time: null,
            place: null,
            completeRank: null,
            points: null,
            vk: false,
            group: 'M21',
            normalizedGroupName: 'm21',
            distanceLength: 1000,
            distancePoints: 10,
        )]);

        $this->assertSame('', $lines[0]->rank);
    }
}
