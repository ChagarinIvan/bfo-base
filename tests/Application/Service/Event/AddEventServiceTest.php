<?php

declare(strict_types=1);

namespace Tests\Application\Service\Event;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\EventDto;
use App\Application\Dto\Event\EventInfoDto;
use App\Application\Service\Event\AddEvent;
use App\Application\Service\Event\AddEventService;
use App\Domain\Event\EventFactory;
use App\Domain\Event\EventInfo;
use App\Domain\Event\EventInput;
use App\Domain\Event\EventRepository;
use App\Models\Event;
use Carbon\Carbon;
use PHPUnit\Framework\MockObject\MockObject;
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
        );
    }

    /** @test */
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

        /** @var Event $event */
        $event = Event::factory()->makeOne();

        $this->factory
            ->expects($this->once())
            ->method('create')
            ->with($this->equalTo($input))
            ->willReturn($event)
        ;

        $this->events
            ->expects($this->once())
            ->method('add')
            ->with($this->identicalTo($event))
        ;

        $dto = new EventDto();
        $infoDto = new EventInfoDto();
        $infoDto->name = 'test event';
        $infoDto->description = 'test event description';
        $infoDto->date = '2023-01-01';
        $dto->info = $infoDto;
        $dto->competitionId = '1';

        $command = new AddEvent($dto, new UserId(1));
        $eventDto = $this->service->execute($command);

        $this->assertEquals($event->id, $eventDto->id);
    }
}
