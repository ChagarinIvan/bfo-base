<?php

declare(strict_types=1);

namespace Tests\Feature\Event;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Event\EventInfoDto;
use App\Application\Dto\Event\EventProtocolDto;
use App\Application\Dto\Event\UpdateEventDto;
use App\Application\Handler\Event\DisableEventHandler;
use App\Application\Handler\Event\ParseEventProtocolHandler;
use App\Application\Handler\Event\UpdateEventProtocolHandler;
use App\Application\Service\Event\CleanupEventResults;
use App\Application\Service\Event\CleanupEventResultsService;
use App\Application\Service\Event\Exception\EventNotFound;
use App\Application\Service\Event\IdentifyProtocolLines;
use App\Application\Service\Event\IdentifyProtocolLinesService;
use App\Application\Service\Event\ParseEventProtocol;
use App\Application\Service\Event\ParseEventProtocolService;
use App\Application\Service\Event\UpdateEvent;
use App\Application\Service\Event\UpdateEventRanks;
use App\Application\Service\Event\UpdateEventRanksService;
use App\Application\Service\Event\UpdateEventService;
use App\Domain\Auth\Impression;
use App\Domain\Competition\Competition;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupCacheInvalidator;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Event\Event\EventDisabled;
use App\Domain\Event\Event\EventProcessingFailed;
use App\Domain\Event\Event\EventProtocolCleaned;
use App\Domain\Event\Event\EventProtocolUpdated;
use App\Domain\Event\EventProcessingStatus;
use App\Domain\Event\ProtocolParser;
use App\Domain\Event\ProtocolUpdater;
use App\Domain\Group\Group;
use App\Domain\Person\Person;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineInput;
use App\Domain\Rank\Rank;
use Carbon\Carbon;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ReplaceEventProtocolTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function cleanup_fails_when_the_event_does_not_exist(): void
    {
        $this->expectException(EventNotFound::class);

        $this->app->make(CleanupEventResultsService::class)
            ->execute(new CleanupEventResults(999999, 'missing-token'));
    }

    #[Test]
    public function disabling_an_event_cleans_up_its_results_and_rebuilds_ranks(): void
    {
        Queue::fake();
        $competition = Competition::factory()->createOne();
        $event = Event::factory()->createOne([
            'competition_id' => $competition->id,
            'processing_status' => EventProcessingStatus::PARSING,
        ]);
        $cup = Cup::factory()->createOne();
        CupEvent::factory()->createOne(['cup_id' => $cup->getKey(), 'event_id' => $event->id]);
        $group = Group::factory()->createOne();
        $distance = Distance::factory()->createOne(['event_id' => $event->id, 'group_id' => $group->id]);
        $person = Person::factory()->createOne(['current_rank' => Rank::FirstRank]);
        $line = ProtocolLine::factory()->createOne([
            'distance_id' => $distance->id,
            'person_id' => $person->id,
            'complete_rank' => 'I',
        ]);

        $event->disable(new Impression(Carbon::parse('2026-10-04 12:00:00'), 17));
        $event->save();
        $invalidator = $this->createMock(CupCacheInvalidator::class);
        $invalidator->expects($this->once())->method('invalidate');
        $this->app->instance(CupCacheInvalidator::class, $invalidator);
        $this->app->make(DisableEventHandler::class)->handle(new EventDisabled($event));

        $this->assertDatabaseHas('events', ['id' => $event->id, 'active' => false]);
        $this->assertDatabaseMissing('distances', ['id' => $distance->id]);
        $this->assertDatabaseMissing('protocol_lines', ['id' => $line->id]);
        $this->assertDatabaseHas('groups', ['id' => $group->id]);
        $this->assertDatabaseHas('person', ['id' => $person->id]);
        $this->assertSame(Rank::WithoutRank, $person->fresh()->current_rank);
        Queue::assertNotPushed(CallQueuedListener::class, static fn (CallQueuedListener $job): bool => $job->class === ParseEventProtocolHandler::class);
    }

    #[Test]
    public function a_failed_stage_cleans_up_its_protocol_and_rebuilds_ranks(): void
    {
        Queue::fake();
        $competition = Competition::factory()->createOne();
        $event = Event::factory()->createOne([
            'competition_id' => $competition->id,
            'processing_status' => EventProcessingStatus::IDENTIFYING_ERROR,
            'processing_token' => 'failed-token',
            'error_message' => 'Identification failed',
        ]);
        $group = Group::factory()->createOne();
        $distance = Distance::factory()->createOne(['event_id' => $event->id, 'group_id' => $group->id]);
        $person = Person::factory()->createOne(['current_rank' => Rank::FirstRank]);
        $line = ProtocolLine::factory()->createOne([
            'distance_id' => $distance->id,
            'person_id' => $person->id,
            'complete_rank' => 'I',
        ]);

        $invalidator = $this->createMock(CupCacheInvalidator::class);
        $invalidator->expects($this->never())->method('invalidate');
        $this->app->instance(CupCacheInvalidator::class, $invalidator);
        $this->app->make(DisableEventHandler::class)
            ->handle(new EventProcessingFailed($event, EventProcessingStatus::IDENTIFYING_ERROR));

        $this->assertDatabaseMissing('distances', ['id' => $distance->id]);
        $this->assertDatabaseMissing('protocol_lines', ['id' => $line->id]);
        $this->assertDatabaseHas('groups', ['id' => $group->id]);
        $this->assertDatabaseHas('person', ['id' => $person->id]);
        $this->assertSame(Rank::WithoutRank, $person->fresh()->current_rank);
        $this->assertSame(EventProcessingStatus::IDENTIFYING_ERROR, $event->fresh()->processing_status);
        Queue::assertNotPushed(CallQueuedListener::class, static fn (CallQueuedListener $job): bool => $job->class === ParseEventProtocolHandler::class);
    }

    #[Test]
    public function cleaning_a_failed_cup_event_invalidates_the_cup_cache_without_starting_parsing(): void
    {
        Queue::fake();
        $competition = Competition::factory()->createOne();
        $event = Event::factory()->createOne([
            'competition_id' => $competition->id,
            'processing_status' => EventProcessingStatus::PARSING_ERROR,
            'processing_token' => 'failed-token',
        ]);
        $cup = Cup::factory()->createOne();
        CupEvent::factory()->createOne(['cup_id' => $cup->getKey(), 'event_id' => $event->id]);

        $invalidator = $this->createMock(CupCacheInvalidator::class);
        $invalidator->expects($this->once())->method('invalidate');
        $this->app->instance(CupCacheInvalidator::class, $invalidator);
        $this->app->make(DisableEventHandler::class)
            ->handle(new EventProcessingFailed($event, EventProcessingStatus::PARSING_ERROR));

        Queue::assertNotPushed(CallQueuedListener::class, static fn (CallQueuedListener $job): bool => $job->class === ParseEventProtocolHandler::class);
    }

    #[Test]
    public function a_delayed_failure_does_not_delete_results_of_a_newer_protocol(): void
    {
        Queue::fake();
        $competition = Competition::factory()->createOne();
        $event = Event::factory()->createOne([
            'competition_id' => $competition->id,
            'processing_status' => EventProcessingStatus::PARSING,
            'processing_token' => 'new-token',
        ]);
        $group = Group::factory()->createOne();
        $distance = Distance::factory()->createOne(['event_id' => $event->id, 'group_id' => $group->id]);
        $person = Person::factory()->createOne(['current_rank' => Rank::FirstRank]);
        $line = ProtocolLine::factory()->createOne([
            'distance_id' => $distance->id,
            'person_id' => $person->id,
            'complete_rank' => 'I',
        ]);

        $previousEvent = clone $event;
        $previousEvent->processing_status = EventProcessingStatus::IDENTIFYING_ERROR;
        $previousEvent->processing_token = 'old-token';
        $failure = new EventProcessingFailed($previousEvent, EventProcessingStatus::IDENTIFYING_ERROR);

        $this->app->make(DisableEventHandler::class)->handle($failure);

        $this->assertSame($event->id, $failure->eventId);
        $this->assertSame('old-token', $failure->processingToken);
        $this->assertDatabaseHas('distances', ['id' => $distance->id]);
        $this->assertDatabaseHas('protocol_lines', ['id' => $line->id]);
        $this->assertSame(Rank::FirstRank, $person->fresh()->current_rank);
        $this->assertSame(EventProcessingStatus::PARSING, $event->fresh()->processing_status);
    }

    #[Test]
    public function replacement_clears_old_results_and_rebuilds_affected_ranks_before_parsing(): void
    {
        Queue::fake();
        $competition = Competition::factory()->createOne();
        $event = Event::factory()->createOne([
            'competition_id' => $competition->id,
            'date' => '2026-09-01',
            'file' => 'old-protocol.html',
            'processing_status' => EventProcessingStatus::REBUILDING_RANKS_ERROR,
            'processing_token' => 'old-token',
            'error_message' => 'Earlier failure',
        ]);
        $group = Group::factory()->createOne(['name' => 'Old group']);
        $distance = Distance::factory()->createOne(['id' => 1001, 'event_id' => $event->id, 'group_id' => $group->id]);
        $person = Person::factory()->createOne(['id' => 501, 'active' => true, 'current_rank' => Rank::FirstRank]);
        $removedPerson = Person::factory()->createOne(['id' => 502, 'active' => true, 'current_rank' => Rank::FirstRank]);
        $line = ProtocolLine::factory()->createOne([
            'id' => 901,
            'distance_id' => $distance->id,
            'person_id' => $person->id,
            'complete_rank' => 'I',
        ]);
        ProtocolLine::factory()->createOne([
            'id' => 902,
            'distance_id' => $distance->id,
            'person_id' => $removedPerson->id,
            'complete_rank' => 'I',
        ]);
        $otherEvent = Event::factory()->createOne([
            'competition_id' => $competition->id,
            'date' => '2026-09-02',
            'processing_status' => EventProcessingStatus::READY,
        ]);
        $otherDistance = Distance::factory()->createOne([
            'id' => 1002,
            'event_id' => $otherEvent->id,
            'group_id' => $group->id,
        ]);
        $otherLine = ProtocolLine::factory()->createOne([
            'id' => 903,
            'distance_id' => $otherDistance->id,
            'person_id' => $person->id,
            'complete_rank' => 'II',
            'activate_rank' => '2026-09-02',
        ]);

        $updater = $this->createMock(ProtocolUpdater::class);
        $updater->expects($this->once())->method('update')->willReturn('new-protocol.html');
        $this->app->instance(ProtocolUpdater::class, $updater);
        $info = new EventInfoDto();
        $info->name = $event->name;
        $info->description = $event->description;
        $info->date = '2026-09-01';
        $protocol = new EventProtocolDto();
        $protocol->protocol = UploadedFile::fake()->createWithContent('replacement.html', '<html>protocol</html>');
        $dto = new UpdateEventDto();
        $dto->info = $info;
        $dto->protocol = $protocol;

        $updated = $this->app->make(UpdateEventService::class)
            ->execute(new UpdateEvent((string) $event->id, $dto, new UserId(17)));

        $this->assertSame(EventProcessingStatus::PARSING->value, $updated->processingStatus);
        $this->assertNull($updated->errorMessage);
        Queue::assertPushed(CallQueuedListener::class, static fn (CallQueuedListener $job): bool => $job->class === UpdateEventProtocolHandler::class);
        Queue::assertNotPushed(CallQueuedListener::class, static fn (CallQueuedListener $job): bool => $job->class === ParseEventProtocolHandler::class);
        $event->refresh();
        $this->assertNotSame('old-token', $event->processing_token);

        $parser = $this->createMock(ProtocolParser::class);
        $parser->expects($this->once())->method('parse')->willReturnCallback(function () use ($distance, $line, $person, $removedPerson, $otherLine): array {
            $this->assertDatabaseMissing('distances', ['id' => $distance->id]);
            $this->assertDatabaseMissing('protocol_lines', ['id' => $line->id]);
            $this->assertDatabaseHas('protocol_lines', ['id' => $otherLine->id]);
            $this->assertSame(Rank::SecondRank, $person->fresh()->current_rank);
            $this->assertSame(Rank::WithoutRank, $removedPerson->fresh()->current_rank);

            return [new ProtocolLineInput(
                serialNumber: 1,
                lastname: 'New',
                firstname: 'Runner',
                club: '',
                year: 2000,
                rank: null,
                runnerNumber: 1,
                time: '00:30:00',
                place: 1,
                completeRank: null,
                points: null,
                vk: false,
                group: 'New group',
                normalizedGroupName: 'newgroup',
                distanceLength: 5000,
                distancePoints: 100,
            )];
        });
        $this->app->instance(ProtocolParser::class, $parser);
        $service = $this->app->make(ParseEventProtocolService::class);

        $service->execute(new ParseEventProtocol($event->id, 'old-token', 17));
        $this->assertDatabaseHas('protocol_lines', ['id' => $line->id]);

        $this->app->make(UpdateEventProtocolHandler::class)
            ->handle(new EventProtocolUpdated($event->id, $event->processing_token, new Impression(Carbon::parse('2026-10-04 12:00:00'), 17)));

        $event->refresh();
        $this->assertSame(EventProcessingStatus::PARSING, $event->processing_status);
        $this->assertDatabaseMissing('distances', ['id' => $distance->id]);
        $this->assertDatabaseMissing('protocol_lines', ['id' => $line->id]);
        $this->assertSame(Rank::SecondRank, $person->fresh()->current_rank);
        $this->assertSame(Rank::WithoutRank, $removedPerson->fresh()->current_rank);
        Queue::assertPushed(CallQueuedListener::class, static fn (CallQueuedListener $job): bool => $job->class === ParseEventProtocolHandler::class);

        $this->app->make(ParseEventProtocolHandler::class)
            ->handle(new EventProtocolCleaned($event->id, $event->processing_token, $event->updated));

        $this->assertSame(EventProcessingStatus::IDENTIFYING, $event->fresh()->processing_status);
        $this->assertDatabaseHas('groups', ['id' => $group->id]);
        $this->assertDatabaseHas('person', ['id' => $person->id]);
        $this->assertDatabaseMissing('protocol_lines', ['id' => $line->id]);
        $this->assertDatabaseHas('protocol_lines', ['lastname' => 'New']);
        $this->assertSame(Rank::SecondRank, $person->fresh()->current_rank);
        $this->assertSame(Rank::WithoutRank, $removedPerson->fresh()->current_rank);

        $this->app->make(UpdateEventProtocolHandler::class)
            ->handle(new EventProtocolUpdated($event->id, $event->processing_token, $event->updated));
        $this->assertDatabaseHas('protocol_lines', ['lastname' => 'New']);

        $impression = new Impression(Carbon::parse('2026-10-04 12:00:00'), 17);
        $this->app->make(IdentifyProtocolLinesService::class)
            ->execute(new IdentifyProtocolLines($event->id, 'old-token', $impression));
        $this->assertSame(EventProcessingStatus::IDENTIFYING, $event->fresh()->processing_status);

        $this->app->make(IdentifyProtocolLinesService::class)
            ->execute(new IdentifyProtocolLines($event->id, $event->processing_token, $impression));
        $this->assertSame(EventProcessingStatus::REBUILDING_RANKS, $event->fresh()->processing_status);
        $this->assertNotNull(ProtocolLine::query()->where('lastname', 'New')->sole()->person_id);

        $this->app->make(UpdateEventRanksService::class)
            ->execute(new UpdateEventRanks($event->id, $event->processing_token, $impression));
        $this->assertSame(EventProcessingStatus::READY, $event->fresh()->processing_status);
    }
}
