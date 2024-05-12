<?php

declare(strict_types=1);

namespace Tests\Application\Service\CupEvent;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\CupEvent\DisableCupEvent;
use App\Application\Service\CupEvent\DisableCupEventService;
use App\Application\Service\CupEvent\Exception\CupEventNotFound;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\FrozenClock;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class DisableCupEventServiceTest extends TestCase
{
    private DisableCupEventService $service;

    private CupEventRepository&MockObject $cupsEvents;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DisableCupEventService(
            $this->cupsEvents = $this->createMock(CupEventRepository::class),
            new FrozenClock,
            new DummyTransactional,
        );
    }

    /** @test */
    public function it_fails_when_cup_event_not_found(): void
    {
        $this->expectException(CupEventNotFound::class);

        $this->cupsEvents
            ->expects($this->once())
            ->method('lockById')
            ->with($this->equalTo(1))
            ->willReturn(null)
        ;

        $command = new DisableCupEvent('1', new UserId(1));
        $this->service->execute($command);
    }

    /** @test */
    public function it_disables_cup_event(): void
    {
        /** @var CupEvent $cupEvent */
        $cupEvent = CupEvent::factory()->makeOne();

        $this->cupsEvents
            ->expects($this->once())
            ->method('lockById')
            ->with($this->equalTo(1))
            ->willReturn($cupEvent)
        ;

        $this->cupsEvents
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($cupEvent))
        ;

        $command = new DisableCupEvent('1', new UserId(1));

        $this->service->execute($command);

        $this->assertFalse($cupEvent->active);
    }
}
