<?php

declare(strict_types=1);

namespace Tests\Application\Service\Event;

use App\Application\Handler\Event\UpdateEventRanksHandler;
use App\Application\Service\Event\Exception\EventNotFound;
use App\Application\Service\Event\UpdateEventRanks;
use App\Application\Service\Event\UpdateEventRanksService;
use App\Domain\Auth\Impression;
use App\Domain\Event\Event;
use App\Domain\Event\Event\EventIdentified;
use App\Domain\Event\EventRepository;
use App\Domain\Person\EventPersonRankUpdater;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\FrozenClock;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UpdateEventRanksServiceTest extends TestCase
{
    private EventRepository&MockObject $events;
    private EventPersonRankUpdater&MockObject $updater;
    private UpdateEventRanksService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->events = $this->createMock(EventRepository::class);
        $this->updater = $this->createMock(EventPersonRankUpdater::class);
        $this->service = new UpdateEventRanksService(
            $this->events,
            $this->updater,
            new DummyTransactional,
            new FrozenClock(Carbon::parse('2026-10-02 12:00:00')),
        );
    }

    #[Test]
    public function it_handles_identification_completion_and_saves_the_event_after_rank_update(): void
    {
        $event = $this->createMock(Event::class);
        $this->events->expects($this->once())->method('lockById')->with(17)->willReturn($event);
        $event->expects($this->once())->method('updateRanks')->with(
            'generation-token',
            $this->updater,
            new Impression(Carbon::parse('2026-10-02 12:00:00'), 42),
        );
        $this->events->expects($this->once())->method('update')->with($event);
        $handler = new UpdateEventRanksHandler($this->service);
        $this->assertInstanceOf(ShouldQueueAfterCommit::class, $handler);

        $handler->handle(new EventIdentified(
            17,
            'generation-token',
            new Impression(Carbon::parse('2026-10-02 11:00:00'), 42),
        ));
    }

    #[Test]
    public function it_fails_without_mutation_when_the_event_is_missing(): void
    {
        $this->events->expects($this->once())->method('lockById')->with(17)->willReturn(null);
        $this->events->expects($this->never())->method('update');
        $this->updater->expects($this->never())->method('update');
        $this->expectException(EventNotFound::class);

        $this->service->execute(new UpdateEventRanks(
            17,
            'generation-token',
            new Impression(Carbon::parse('2026-10-02 11:00:00'), 42),
        ));
    }
}
