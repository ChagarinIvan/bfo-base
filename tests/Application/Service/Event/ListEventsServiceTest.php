<?php

declare(strict_types=1);

namespace Tests\Application\Service\Event;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\SearchEventDto;
use App\Application\Service\Event\ListEvents;
use App\Application\Service\Event\ListEventsService;
use App\Domain\Event\Event;
use App\Domain\Event\EventRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use Pagerfanta\Adapter\ArrayAdapter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ListEventsServiceTest extends TestCase
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

    #[Test]
    public function it_gets_a_paginated_compact_list_of_events(): void
    {
        /** @var list<Event> $events */
        $events = Event::factory(count: 2)->make()->all();
        $events[0]->setAttribute('protocol_lines_count', 3);
        $events[1]->setAttribute('protocol_lines_count', 5);

        $this->events
            ->expects($this->once())
            ->method('paginate')
            ->with(new Criteria(['competitionId' => '1']))
            ->willReturn(new Slice(new ArrayAdapter($events)))
        ;

        $result = $this->service->execute(new ListEvents(new SearchEventDto('1')));
        $items = $result->items();

        $this->assertInstanceOf(Slice::class, $result);
        $this->assertSame(5, $items[1]->participantsCount);
        $this->assertSame((string) $events[1]->id, $items[1]->id);
    }
}
