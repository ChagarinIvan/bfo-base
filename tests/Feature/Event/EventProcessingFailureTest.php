<?php

declare(strict_types=1);

namespace Tests\Feature\Event;

use App\Application\Service\Event\CleanupEventResults;
use App\Application\Service\Event\CleanupEventResultsService;
use App\Application\Service\Event\IdentifyProtocolLines;
use App\Application\Service\Event\IdentifyProtocolLinesService;
use App\Application\Service\Event\ParseEventProtocol;
use App\Application\Service\Event\ParseEventProtocolService;
use App\Application\Service\Event\UpdateEventRanks;
use App\Application\Service\Event\UpdateEventRanksService;
use App\Domain\Auth\Impression;
use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Event\Event\EventProcessingFailed;
use App\Domain\Event\EventProcessingStatus;
use App\Domain\Event\EventRepository;
use App\Domain\Event\EventResultsCleaner;
use App\Domain\Event\Exception\EventResultsCleanupError;
use App\Domain\Event\ProtocolParser;
use App\Domain\Group\Group;
use App\Domain\Person\EventPersonRankUpdater;
use App\Domain\Person\Person;
use App\Domain\Person\RankFactsCollector;
use App\Domain\ProtocolLine\Factory\ProtocolLinesFactory;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineIdentifier;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event as EventFacade;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;
use TypeError;

final class EventProcessingFailureTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_replacement_cleanup_failure_records_an_error_after_rolling_back_results(): void
    {
        EventFacade::fake();
        $event = $this->event(EventProcessingStatus::PARSING);
        $group = Group::factory()->createOne();
        $distance = Distance::factory()->createOne(['event_id' => $event->id, 'group_id' => $group->id]);
        $person = Person::factory()->createOne();
        $line = ProtocolLine::factory()->createOne(['distance_id' => $distance->id, 'person_id' => $person->id]);
        $collector = $this->createMock(RankFactsCollector::class);
        $failure = new RuntimeException('private detail');
        $collector->expects($this->exactly(2))->method('collect')->willThrowException($failure);
        $this->app->instance(RankFactsCollector::class, $collector);

        try {
            $this->app->make(EventResultsCleaner::class)->clean($event, $this->impression());
            self::fail('Expected a domain cleanup error.');
        } catch (EventResultsCleanupError $exception) {
            $this->assertSame($failure, $exception->getPrevious());
            $this->assertSame('Ошибка подготовки к замене протокола. Свяжитесь с администратором.', $exception->getMessage());
        }

        $this->app->make(CleanupEventResultsService::class)->execute(new CleanupEventResults(
            $event->id,
            $event->processing_token,
            startParsing: true,
        ));

        $this->assertDatabaseHas('distances', ['id' => $distance->id]);
        $this->assertDatabaseHas('protocol_lines', ['id' => $line->id]);
        $this->assertSame(EventProcessingStatus::PARSING_ERROR, $event->fresh()->processing_status);
        $this->assertSame('Ошибка подготовки к замене протокола. Свяжитесь с администратором.', $event->fresh()->error_message);
        EventFacade::assertDispatchedTimes(EventProcessingFailed::class, 1);

        $this->app->make(CleanupEventResultsService::class)->execute(new CleanupEventResults(
            $event->id,
            $event->processing_token,
            startParsing: true,
        ));
        EventFacade::assertDispatchedTimes(EventProcessingFailed::class, 1);
    }

    #[Test]
    public function an_unexpected_cleanup_type_error_is_not_converted_to_a_processing_error(): void
    {
        EventFacade::fake();
        $event = $this->event(EventProcessingStatus::PARSING);
        $cleaner = $this->createMock(EventResultsCleaner::class);
        $failure = new TypeError('programming error');
        $cleaner->expects($this->once())->method('clean')->willThrowException($failure);
        $this->app->instance(EventResultsCleaner::class, $cleaner);

        try {
            $this->app->make(CleanupEventResultsService::class)->execute(new CleanupEventResults(
                $event->id,
                $event->processing_token,
                startParsing: true,
            ));
            self::fail('Expected the programming error to propagate.');
        } catch (TypeError $exception) {
            $this->assertSame($failure, $exception);
        }

        $this->assertSame(EventProcessingStatus::PARSING, $event->fresh()->processing_status);
        $this->assertNull($event->fresh()->error_message);
        EventFacade::assertNotDispatched(EventProcessingFailed::class);
    }

    #[Test]
    public function a_line_save_failure_rolls_back_the_parse_transaction(): void
    {
        EventFacade::fake();
        $event = $this->event(EventProcessingStatus::PARSING);
        $parser = $this->createMock(ProtocolParser::class);
        $parser->expects($this->once())->method('parse')->willReturn([]);
        $factory = $this->createMock(ProtocolLinesFactory::class);
        $factory->expects($this->once())->method('create')->willReturn([new ProtocolLine]);
        $lines = $this->createMock(ProtocolLineRepository::class);
        $lines->expects($this->once())->method('add')->willThrowException(new RuntimeException('database detail'));
        $service = new ParseEventProtocolService($parser, $factory, $lines, $this->events(), $this->transactional(), $this->clock());
        try {
            $service->execute(new ParseEventProtocol($event->id, $event->processing_token, 42));
            self::fail('Expected a line save failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('database detail', $exception->getMessage());
        }

        $this->assertSame(EventProcessingStatus::PARSING, $event->fresh()->processing_status);
        $this->assertNull($event->fresh()->error_message);
        EventFacade::assertNotDispatched(EventProcessingFailed::class);
    }

    #[Test]
    public function an_unexpected_parser_failure_records_a_safe_parsing_error(): void
    {
        EventFacade::fake();
        $event = $this->event(EventProcessingStatus::PARSING);
        $parser = $this->createMock(ProtocolParser::class);
        $parser->expects($this->once())->method('parse')->willThrowException(new RuntimeException('private detail'));
        $factory = $this->createMock(ProtocolLinesFactory::class);
        $factory->expects($this->never())->method('create');
        $lines = $this->createMock(ProtocolLineRepository::class);
        $lines->expects($this->once())->method('add');
        $service = new ParseEventProtocolService($parser, $factory, $lines, $this->events(), $this->transactional(), $this->clock());
        $service->execute(new ParseEventProtocol($event->id, $event->processing_token, 42));

        $this->assertSame(EventProcessingStatus::PARSING_ERROR, $event->fresh()->processing_status);
        $this->assertSame('Ошибка парсинга протокола. Свяжитесь с администратором.', $event->fresh()->error_message);
        EventFacade::assertDispatchedTimes(EventProcessingFailed::class, 1);
    }

    #[Test]
    public function an_unexpected_identification_failure_records_identifying_error(): void
    {
        EventFacade::fake();
        $event = $this->event(EventProcessingStatus::IDENTIFYING);
        $identifier = $this->createMock(ProtocolLineIdentifier::class);
        $identifier->expects($this->once())->method('identify')->willThrowException(new RuntimeException('private detail'));
        $service = new IdentifyProtocolLinesService($this->events(), $identifier, $this->transactional(), $this->clock());
        $service->execute(new IdentifyProtocolLines($event->id, $event->processing_token, $this->impression()));

        $this->assertSame(EventProcessingStatus::IDENTIFYING_ERROR, $event->fresh()->processing_status);
        $this->assertSame('Ошибка идентификации протокола. Свяжитесь с администратором.', $event->fresh()->error_message);
        EventFacade::assertDispatchedTimes(EventProcessingFailed::class, 1);
    }

    #[Test]
    public function an_unexpected_rank_failure_records_rebuilding_error(): void
    {
        EventFacade::fake();
        $event = $this->event(EventProcessingStatus::REBUILDING_RANKS);
        $updater = $this->createMock(EventPersonRankUpdater::class);
        $updater->expects($this->once())->method('update')->willThrowException(new RuntimeException('private detail'));
        $service = new UpdateEventRanksService($this->events(), $updater, $this->transactional(), $this->clock());
        $service->execute(new UpdateEventRanks($event->id, $event->processing_token, $this->impression()));

        $this->assertSame(EventProcessingStatus::REBUILDING_RANKS_ERROR, $event->fresh()->processing_status);
        $this->assertSame('Ошибка обновления разрядов. Свяжитесь с администратором.', $event->fresh()->error_message);
        EventFacade::assertDispatchedTimes(EventProcessingFailed::class, 1);
    }

    private function event(EventProcessingStatus $status): Event
    {
        $competition = Competition::factory()->createOne();

        return Event::factory()->createOne(['competition_id' => $competition->id, 'processing_status' => $status]);
    }

    private function impression(): Impression
    {
        return new Impression(Carbon::parse('2026-10-04 12:00:00'), 42);
    }

    private function events(): EventRepository
    {
        return $this->app->make(EventRepository::class);
    }

    private function transactional(): TransactionManager
    {
        return $this->app->make(TransactionManager::class);
    }

    private function clock(): Clock
    {
        return $this->app->make(Clock::class);
    }
}
