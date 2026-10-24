<?php

declare(strict_types=1);

namespace Tests\Feature\ProtocolLine;

use App\Domain\Auth\Impression;
use App\Domain\Competition\Competition;
use App\Domain\Event\Event;
use App\Domain\ProtocolLine\Factory\StandardProtocolLinesFactory;
use App\Domain\ProtocolLine\ProtocolLineInput;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use Carbon\Carbon;
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
        $event = Event::factory()->createOne(['competition_id' => $competition->id, 'created_by' => 11, 'updated_by' => 17]);
        $impression = new Impression(Carbon::parse('2026-10-24 10:30:00'), 42);
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
        )], $impression);

        $this->assertSame('', $lines[0]->rank);
        $this->assertEquals($impression, $lines[0]->created);
        $this->assertEquals($impression, $lines[0]->updated);
        $this->app->make(ProtocolLineRepository::class)->add(...$lines);
        $saved = $lines[0]->fresh();
        $this->assertEquals($impression, $saved->created);
        $this->assertEquals($impression, $saved->updated);
    }
}
