<?php

declare(strict_types=1);

namespace Tests\Application\Service\Event;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\EventDto;
use App\Application\Dto\Event\EventInfoDto;
use App\Application\Dto\Event\EventProtocolDto;
use App\Application\Service\Event\AddEvent;
use App\Application\Service\Event\AddEventService;
use App\Domain\Auth\Impression;
use App\Domain\Event\Event;
use App\Domain\Event\EventInfo;
use App\Domain\Event\EventProtocol;
use App\Domain\Event\EventProtocolRepository;
use App\Domain\Event\EventRepository;
use App\Domain\Event\Factory\EventFactory;
use App\Domain\Event\Factory\EventInput;
use App\Domain\Event\Factory\EventProtocolFactory;
use App\Domain\Event\Protocol;
use App\Domain\Event\Protocol\ProtocolFactory;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\TestCase;

final class AddEventServiceTest extends TestCase
{
    private AddEventService $service;

    private EventFactory&MockObject $factory;

    private EventRepository&MockObject $events;

    private EventProtocolFactory&MockObject $eventProtocolsFactory;

    private EventProtocolRepository&MockObject $eventProtocols;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AddEventService(
            $this->factory = $this->createMock(EventFactory::class),
            $this->events = $this->createMock(EventRepository::class),
            new EventAssembler(new AuthAssembler),
            new ProtocolFactory,
            $this->eventProtocolsFactory = $this->createMock(EventProtocolFactory::class),
            $this->eventProtocols = $this->createMock(EventProtocolRepository::class),
        );
    }

    #[Test]
    public function it_creates_event(): void
    {
        $info = new EventInfo(
            name: 'test event',
            description: 'test event description',
            date: new Carbon('2023-01-01'),
        );

        $input = new EventInput(
            $info,
            1,
            1,
        );
        $eventProtocol = new Protocol('content', 'text/html');

        /** @var Event $event */
        $event = Event::factory()->makeOne();
        $event->id = 10;
        $event->file = '2023/protocol.xml';
        $event->created = new Impression(Carbon::parse('2023-01-01'), 1);
        $run = EventProtocol::queue($event->id, 'run-1');
        $run->id = 20;

        $this->factory
            ->expects($this->once())
            ->method('create')
            ->with($input, $eventProtocol)
            ->willReturn($event)
        ;

        $this->events
            ->expects($this->once())
            ->method('add')
            ->with($this->identicalTo($event))
        ;

        $this->eventProtocolsFactory
            ->expects($this->once())
            ->method('create')
            ->with($event->id, $event->created)
            ->willReturn($run)
        ;
        $this->eventProtocols->expects($this->once())->method('add')->with($run);
        $this->events->expects($this->once())->method('update')->with($event);

        $dto = new EventDto();
        $infoDto = new EventInfoDto();
        $infoDto->name = 'test event';
        $infoDto->description = 'test event description';
        $infoDto->date = '2023-01-01';
        $dto->info = $infoDto;
        $protocol = $this->createStub(UploadedFile::class);
        $protocol->method('getContent')->willReturn('content');
        $protocol->method('getMimeType')->willReturn('text/html');

        $protocolDto = new EventProtocolDto;
        $protocolDto->protocol = $protocol;

        $command = new AddEvent(1, $dto, $protocolDto, new UserId(1));
        $eventDto = $this->service->execute($command);

        $this->assertEquals($event->id, $eventDto->id);
    }
}
