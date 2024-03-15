<?php

declare(strict_types=1);

namespace Tests\Application\Service\Event;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Event\DisableEvent;
use App\Application\Service\Event\DisableEventService;
use App\Application\Service\Event\Exception\EventNotFound;
use App\Domain\Event\EventRepository;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\FrozenClock;
use App\Models\Event;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class DisableEventServiceTest extends TestCase
{
    private DisableEventService $service;

    private EventRepository&MockObject $events;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DisableEventService(
            $this->events = $this->createMock(EventRepository::class),
            new FrozenClock(),
            new DummyTransactional(),
        );
    }

    /** @test */
    public function it_fails_when_event_not_found(): void
    {
        $this->expectException(EventNotFound::class);

        $this->events
            ->expects($this->once())
            ->method('lockById')
            ->with($this->equalTo(1))
            ->willReturn(null)
        ;

        $command = new DisableEvent('1', new UserId(1));
        $this->service->execute($command);
    }

    /** @test */
    public function it_disables_event(): void
    {
        /** @var Event $event */
        $event = Event::factory()->makeOne();

        $this->events
            ->expects($this->once())
            ->method('lockById')
            ->with($this->equalTo(1))
            ->willReturn($event)
        ;

        $this->events
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($event))
        ;

        $command = new DisableEvent('1', new UserId(1));
        $this->service->execute($command);

        $this->assertFalse($event->active);
    }
}
