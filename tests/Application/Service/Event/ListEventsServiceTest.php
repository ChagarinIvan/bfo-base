<?php

declare(strict_types=1);

namespace Application\Service\Event;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\EventSearchDto;
use App\Application\Dto\Event\ViewEventDto;
use App\Application\Service\Event\ListEvents;
use App\Application\Service\Event\ListEventsService;
use App\Domain\Event\EventRepository;
use App\Domain\Shared\Criteria;
use App\Models\Event;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class ListEventsServiceTest extends TestCase
{
    private ListEventsService $service;

    private EventRepository&MockObject $events;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ListEventsService(
            $this->events = $this->createMock(EventRepository::class),
            new EventAssembler(new AuthAssembler),
        );
    }

    /** @test */
    public function it_gets_list_of_events(): void
    {
        /** @var Event[] $events */
        $events = Event::factory(count: 2)->make();

        $this->events
            ->expects($this->once())
            ->method('byCriteria')
            ->with($this->equalTo(new Criteria(['competitionId' => '1'])))
            ->willReturn($events)
        ;

        $dto = new EventSearchDto('1');

        $command = new ListEvents($dto);
        $result = $this->service->execute($command);

        $this->assertIsList($result);
        $this->assertContainsOnlyInstancesOf(ViewEventDto::class, $result);
        $this->assertEquals($events[0]->id, $result[1]->id);
    }
}
