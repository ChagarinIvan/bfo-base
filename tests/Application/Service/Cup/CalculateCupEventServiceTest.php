<?php

declare(strict_types=1);

namespace Tests\Application\Service\Cup;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Cup\CupAssembler;
use App\Application\Dto\Event\EventAssembler;
use App\Application\Service\Cup\CalculateCupEvent;
use App\Application\Service\Cup\CalculateCupEventService;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Application\Service\CupEvent\Exception\CupEventNotFound;
use App\Application\Service\Group\Exception\GroupNotFound;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Cup\CupRepository;
use App\Domain\Cup\CupType;
use App\Domain\Event\Event;
use App\Domain\Event\EventRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class CalculateCupEventServiceTest extends TestCase
{
    private CalculateCupEventService $service;

    private CupRepository&MockObject $cups;

    private CupEventRepository&MockObject $cupEvents;

    private EventRepository&MockObject $events;

    protected function setUp(): void
    {
        parent::setUp();

        $authAssembler = new AuthAssembler;
        $this->service = new CalculateCupEventService(
            $this->cups = $this->createMock(CupRepository::class),
            $this->cupEvents = $this->createMock(CupEventRepository::class),
            new CupAssembler(
                $this->events = $this->createMock(EventRepository::class),
                new EventAssembler($authAssembler),
                $authAssembler,
            ),
        );
    }

    /** @test */
    public function it_fails_when_cup_not_found(): void
    {
        $this->expectException(CupNotFound::class);

        $this->cups
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo(1))
            ->willReturn(null)
        ;

        $this->cupEvents->expects($this->never())->method('byId');
        $this->events->expects($this->never())->method('oneByCriteria');

        $command = new CalculateCupEvent('1', '1', 'M_');
        $this->service->execute($command);
    }

    /** @test */
    public function it_fails_when_cup_event_not_found(): void
    {
        $this->expectException(CupEventNotFound::class);

        /** @var Cup $cup */
        $cup = Cup::factory()->makeOne();

        $this->cups
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo(1))
            ->willReturn($cup)
        ;

        $this->cupEvents
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo(1))
            ->willReturn(null)
        ;

        $this->events->expects($this->never())->method('oneByCriteria');

        $command = new CalculateCupEvent('1', '1', 'M_');
        $this->service->execute($command);
    }

    /** @test */
    public function it_fails_when_group_not_found(): void
    {
        $this->expectException(GroupNotFound::class);

        /** @var Cup $cup */
        $cup = Cup::factory()->makeOne();
        /** @var CupEvent $cupEvent */
        $cupEvent = CupEvent::factory()->makeOne();

        $this->cups
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo(1))
            ->willReturn($cup)
        ;

        $this->cupEvents
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo(1))
            ->willReturn($cupEvent)
        ;

        $this->events->expects($this->never())->method('oneByCriteria');

        $command = new CalculateCupEvent('1', '1', 'test');
        $this->service->execute($command);
    }

    /** @test */
    public function it_calculates_cup_event(): void
    {
        /** @var Cup $cup */
        $cup = Cup::factory(state: ['name' => 'test', 'year' => 2024, 'type' => CupType::SPRINT])->makeOne();
        /** @var CupEvent $cupEvent */
        $cupEvent = CupEvent::factory(state: ['points' => 0.9])->makeOne();
        /** @var Event $event */
        $event = Event::factory()->makeOne();
        $cupEvent->event = $event;
        $cupEvent->cup = $cup;
        $cup->events->add($cupEvent);

        $this->cups
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo(1))
            ->willReturn($cup)
        ;

        $this->cupEvents
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo(1))
            ->willReturn($cupEvent)
        ;

        $this->events->expects($this->never())->method('oneByCriteria');

        $command = new CalculateCupEvent('1', '1', 'M_0_');
        $result = $this->service->execute($command);

        $this->assertEquals('test', $result->cupName);
        $this->assertEquals(2024, $result->cupYear);
        $this->assertCount(2, $result->cupGroups);
        $this->assertEquals('0.9', $result->cupEvent->points);
    }
}
