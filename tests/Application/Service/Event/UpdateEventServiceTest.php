<?php

declare(strict_types=1);

namespace Tests\Application\Service\Event;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\EventInfoDto;
use App\Application\Dto\Event\EventProtocolDto;
use App\Application\Dto\Event\UpdateEventDto;
use App\Application\Service\Event\Exception\EventNotFound;
use App\Application\Service\Event\Exception\InvalidProtocol;
use App\Application\Service\Event\UpdateEvent;
use App\Application\Service\Event\UpdateEventService;
use App\Domain\Event\Event;
use App\Domain\Event\Event\EventInfoUpdated;
use App\Domain\Event\Event\EventProtocolUpdated;
use App\Domain\Event\EventRepository;
use App\Domain\Event\Protocol;
use App\Domain\Event\Protocol\ProtocolFactory;
use App\Domain\Event\ProtocolUpdater;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\FrozenClock;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\TestCase;

final class UpdateEventServiceTest extends TestCase
{
    private UpdateEventService $service;

    private MockObject&ProtocolUpdater $updater;

    private EventRepository&MockObject $events;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new UpdateEventService(
            new FrozenClock(Carbon::parse('2023-04-01')),
            $this->updater = $this->createMock(ProtocolUpdater::class),
            $this->events = $this->createMock(EventRepository::class),
            new EventAssembler(new AuthAssembler),
            new DummyTransactional,
            new ProtocolFactory,
        );
    }

    #[Test]
    public function it_fails_when_event_not_found(): void
    {
        $this->expectException(EventNotFound::class);

        $this->events
            ->expects($this->once())
            ->method('lockById')
            ->with(1)
            ->willReturn(null)
        ;

        $this->updater->expects($this->never())->method('update');

        $info = new EventInfoDto;
        $info->name = 'title';
        $info->description = 'description';
        $info->date = '2024-01-01';

        $dto = new UpdateEventDto;
        $dto->info = $info;

        $command = new UpdateEvent('1', $dto, new UserId(1));
        $this->service->execute($command);
    }

    #[Test]
    public function it_updates_event_info(): void
    {
        /** @var Event $event */
        $event = Event::factory()->makeOne();

        $this->events
            ->expects($this->once())
            ->method('lockById')
            ->with(1)
            ->willReturn($event)
        ;

        $this->events->expects($this->once())->method('update');
        $this->updater->expects($this->never())->method('update');

        $info = new EventInfoDto;
        $info->name = 'title';
        $info->description = 'description';
        $info->date = '1989-07-01';

        $dto = new UpdateEventDto;
        $dto->info = $info;

        $command = new UpdateEvent('1', $dto, new UserId(1));
        $eventDto = $this->service->execute($command);

        $this->assertSame('title', $eventDto->name);
        $this->assertSame('description', $eventDto->description);
        $this->assertSame('1989-07-01', $eventDto->date);
        $this->assertEquals('1', $eventDto->updated->by);
        $this->assertSame('2023-04-01T00:00:00+00:00', $eventDto->updated->at);

        $events = $event->releasedEvents();
        $this->assertCount(1, $events);
        $this->assertContainsOnlyInstancesOf(EventInfoUpdated::class, $events);
    }

    #[Test]
    public function it_updates_event_protocol(): void
    {
        /** @var Event $event */
        $event = Event::factory()->makeOne();

        $this->events
            ->expects($this->once())
            ->method('lockById')
            ->with(1)
            ->willReturn($event)
        ;

        $this->events->expects($this->once())->method('update');
        $this->updater
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($event), new Protocol('content', 'html'))
            ->willReturn('2023/2023-01-01_test_event.text/html')
        ;

        $info = new EventInfoDto;
        $info->name = 'title';
        $info->description = 'description';
        $info->date = '1989-07-01';

        $protocol = $this->createMock(UploadedFile::class);
        $protocol->method('getContent')->willReturn('content');
        $protocol->method('getMimeType')->willReturn('html');

        $dto = new UpdateEventDto;
        $dto->info = $info;
        $dto->protocol = new EventProtocolDto;
        $dto->protocol->protocol = $protocol;

        $command = new UpdateEvent('1', $dto, new UserId(1));
        $this->service->execute($command);

        $events = $event->releasedEvents();
        $this->assertCount(2, $events);
        $this->assertInstanceOf(EventInfoUpdated::class, $events[0]);
        $this->assertInstanceOf(EventProtocolUpdated::class, $events[1]);
    }

    #[Test]
    public function it_maps_invalid_protocol_content_to_an_application_error(): void
    {
        $this->expectException(InvalidProtocol::class);

        /** @var Event $event */
        $event = Event::factory()->makeOne();
        $this->events->method('lockById')->willReturn($event);

        $info = new EventInfoDto;
        $info->name = 'title';
        $info->description = 'description';
        $info->date = '1989-07-01';

        $protocol = new EventProtocolDto;
        $protocol->url = 'data://text/plain,invalid';

        $dto = new UpdateEventDto;
        $dto->info = $info;
        $dto->protocol = $protocol;

        $this->service->execute(new UpdateEvent('1', $dto, new UserId(1)));
    }
}
