<?php

declare(strict_types=1);

namespace Tests\Application\Service\Event;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\ViewEventDto;
use App\Application\Service\Event\Exception\EventNotFound;
use App\Application\Service\Event\ViewEvent;
use App\Application\Service\Event\ViewEventService;
use App\Domain\Event\Event;
use App\Domain\Event\EventRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ViewEventServiceTest extends TestCase
{
    private ViewEventService $service;

    private EventRepository&MockObject $events;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ViewEventService(
            $this->events = $this->createMock(EventRepository::class),
            new EventAssembler(new AuthAssembler),
        );
    }

    /** @test */
    public function it_fails_when_event_not_found(): void
    {
        $this->expectException(EventNotFound::class);

        $this->events
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo(1))
            ->willReturn(null)
        ;

        $command = new ViewEvent('1');
        $this->service->execute($command);
    }

    /** @test */
    public function it_shows_event(): void
    {
        /** @var Event $event */
        $event = Event::factory()->makeOne();

        $this->events
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo(1))
            ->willReturn($event)
        ;

        $command = new ViewEvent('1');
        $result = $this->service->execute($command);

        $this->assertInstanceOf(ViewEventDto::class, $result);
        $this->assertEquals($event->id, $result->id);
    }
}
