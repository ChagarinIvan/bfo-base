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
use App\Domain\Event\EventProcessingStatus;
use App\Domain\Event\EventRepository;
use App\Domain\Event\Factory\EventFactory;
use App\Domain\Event\Factory\EventInput;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AddEventService(
            $this->factory = $this->createMock(EventFactory::class),
            $this->events = $this->createMock(EventRepository::class),
            new EventAssembler(new AuthAssembler),
            new ProtocolFactory,
        );
    }

    #[Test]
    public function it_creates_event(): void
    {
        $info = new EventInfo('test event', 'test event description', Carbon::parse('2023-01-01'));
        $input = new EventInput($info, 1, 1);
        $protocol = new Protocol('content', 'text/html');
        $impression = new Impression(Carbon::parse('2023-01-01'), 1);
        $event = $this->createMock(Event::class);
        $event->expects($this->atLeast(9))->method('__get')->willReturnMap([
            ['id', 10],
            ['competition_id', 1],
            ['name', 'test event'],
            ['description', 'test event description'],
            ['date', $info->date],
            ['created', $impression],
            ['updated', $impression],
            ['processing_status', EventProcessingStatus::PARSING],
            ['error_message', null],
        ]);

        $this->factory->expects($this->once())->method('create')->with($input, $protocol)->willReturn($event);
        $this->events->expects($this->once())->method('add')->with($this->identicalTo($event));
        $this->events->expects($this->never())->method('update');

        $dto = new EventDto();
        $infoDto = new EventInfoDto();
        $infoDto->name = 'test event';
        $infoDto->description = 'test event description';
        $infoDto->date = '2023-01-01';
        $dto->info = $infoDto;
        $file = $this->createStub(UploadedFile::class);
        $file->method('getContent')->willReturn('content');
        $file->method('getMimeType')->willReturn('text/html');
        $protocolDto = new EventProtocolDto();
        $protocolDto->protocol = $file;

        $result = $this->service->execute(new AddEvent(1, $dto, $protocolDto, new UserId(1)));

        $this->assertSame('10', $result->id);
        $this->assertSame('parsing', $result->processingStatus);
    }
}
