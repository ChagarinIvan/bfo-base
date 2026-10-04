<?php

declare(strict_types=1);

namespace Tests\Feature\Event;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Event\UniteEventsDto;
use App\Application\Service\Event\UniteEvents;
use App\Application\Service\Event\UniteEventsService;
use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Event\Event\EventCreated;
use App\Domain\Event\Event\EventParsingStarted;
use App\Domain\Event\EventProcessingStatus;
use App\Domain\Event\EventRepository;
use App\Domain\Group\Group;
use App\Domain\Person\Person;
use App\Domain\Person\RankFactsCollector;
use App\Domain\ProtocolLine\ProtocolLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event as EventBus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UniteEventsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function ordinary_event_still_emits_creation_and_parsing_events(): void
    {
        $competition = Competition::factory()->createOne();
        $event = Event::factory()->makeOne([
            'competition_id' => $competition->getKey(),
            'file' => 'protocol.html',
            'processing_status' => EventProcessingStatus::PARSING,
        ]);

        EventBus::fake([EventCreated::class, EventParsingStarted::class]);
        $this->app->make(EventRepository::class)->add($event);

        EventBus::assertDispatched(EventCreated::class);
        EventBus::assertDispatched(EventParsingStarted::class);
    }

    #[Test]
    public function merged_event_is_ready_without_creation_events_or_earned_ranks(): void
    {
        $competition = Competition::factory()->createOne();
        $person = Person::factory()->createOne();
        $group = Group::factory()->createOne();
        $first = Event::factory()->createOne(['competition_id' => $competition->getKey(), 'date' => '2026-05-10']);
        $second = Event::factory()->createOne(['competition_id' => $competition->getKey(), 'date' => '2026-05-11']);
        $firstDistance = Distance::factory()->createOne(['event_id' => $first->getKey(), 'group_id' => $group->getKey()]);
        $secondDistance = Distance::factory()->createOne(['event_id' => $second->getKey(), 'group_id' => $group->getKey()]);
        ProtocolLine::factory()->createOne([
            'distance_id' => $firstDistance->getKey(),
            'person_id' => $person->getKey(),
            'time' => '00:10:00',
            'complete_rank' => 'I',
            'activate_rank' => '2026-05-10',
        ]);
        ProtocolLine::factory()->createOne([
            'distance_id' => $secondDistance->getKey(),
            'person_id' => $person->getKey(),
            'time' => '00:20:00',
            'complete_rank' => 'I',
            'activate_rank' => '2026-05-11',
        ]);

        EventBus::fake([EventCreated::class, EventParsingStarted::class]);
        $input = new UniteEventsDto;
        $input->eventIds = [$first->getKey(), $second->getKey()];
        $result = $this->app->make(UniteEventsService::class)
            ->execute(new UniteEvents($competition->getKey(), $input, new UserId(17)));

        $merged = Event::query()->findOrFail((int) $result->id);
        $this->assertSame(EventProcessingStatus::READY, $merged->processing_status);
        $this->assertSame(EventProcessingStatus::READY->value, $result->processingStatus);
        $this->assertSame('', $merged->file);
        EventBus::assertNotDispatched(EventCreated::class);
        EventBus::assertNotDispatched(EventParsingStarted::class);

        $mergedLine = ProtocolLine::query()
            ->whereHas('distance', static fn ($query) => $query->where('event_id', $merged->id))
            ->sole();
        $this->assertSame($person->getKey(), $mergedLine->person_id);
        $this->assertSame('', $mergedLine->complete_rank);
        $this->assertNull($mergedLine->activate_rank);
        $this->assertCount(2, $this->app->make(RankFactsCollector::class)->collect($person->getKey()));
    }
}
