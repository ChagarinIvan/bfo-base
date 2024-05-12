<?php

declare(strict_types=1);

namespace Tests\Application\Service\Cup;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Cup\CupAssembler;
use App\Application\Dto\Cup\ViewCupDto;
use App\Application\Dto\Event\EventAssembler;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Application\Service\Cup\ViewCup;
use App\Application\Service\Cup\ViewCupService;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupRepository;
use App\Domain\Event\Event;
use App\Domain\Event\EventRepository;
use App\Domain\Shared\Criteria;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ViewCupServiceTest extends TestCase
{
    private ViewCupService $service;

    private CupRepository&MockObject $cups;

    private EventRepository&MockObject $events;

    protected function setUp(): void
    {
        parent::setUp();
        $authAssembler = new AuthAssembler;

        $this->service = new ViewCupService(
            $this->cups = $this->createMock(CupRepository::class),
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

        $command = new ViewCup('1');
        $this->service->execute($command);
    }

    /** @test */
    public function it_shows_cup(): void
    {
        /** @var Cup $cup */
        $cup = Cup::factory()->makeOne();
        /** @var CupEvent $cupEvent */
        $cupEvent = CupEvent::factory()->makeOne();
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

        $this->events
            ->expects($this->once())
            ->method('oneByCriteria')
            ->with($this->equalTo( new Criteria(['cupId' => $cup->id], ['date' => 'desc'])))
            ->willReturn($event)
        ;

        $command = new ViewCup('1');
        $result = $this->service->execute($command);

        $this->assertInstanceOf(ViewCupDto::class, $result);
        $this->assertEquals($cup->id, $result->id);
        $this->assertEquals($event->date->format('Y-m-d'), $result->lastEventDate);
    }
}
