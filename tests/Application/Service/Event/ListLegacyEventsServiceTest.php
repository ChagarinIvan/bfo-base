<?php

declare(strict_types=1);

namespace Tests\Application\Service\Event;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\LegacyViewEventDto;
use App\Application\Dto\Event\SearchEventDto;
use App\Application\Service\Event\ListEvents;
use App\Application\Service\Event\ListLegacyEventsService;
use App\Domain\Event\Event;
use App\Domain\Event\EventRepository;
use App\Domain\Shared\Criteria;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ListLegacyEventsServiceTest extends TestCase
{
    private ListLegacyEventsService $service;

    private EventRepository&MockObject $events;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ListLegacyEventsService(
            $this->events = $this->createMock(EventRepository::class),
            new EventAssembler(new AuthAssembler),
        );
    }

    #[Test]
    public function it_preserves_the_blade_event_dto_path(): void
    {
        /** @var Event[] $events */
        $events = Event::factory(count: 2)->make();

        $this->events
            ->expects($this->once())
            ->method('byCriteria')
            ->with(new Criteria(['competitionId' => '1']))
            ->willReturn($events)
        ;

        $result = $this->service->execute(new ListEvents(new SearchEventDto('1')));

        $this->assertContainsOnlyInstancesOf(LegacyViewEventDto::class, $result);
        $this->assertSame((string) $events[1]->id, $result[1]->id);
    }
}
