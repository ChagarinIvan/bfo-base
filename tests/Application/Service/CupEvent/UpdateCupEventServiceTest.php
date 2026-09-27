<?php

declare(strict_types=1);

namespace Tests\Application\Service\CupEvent;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\CupEvent\CupEventAssembler;
use App\Application\Dto\CupEvent\CupEventDto;
use App\Application\Service\CupEvent\Exception\CupEventAlreadyExists;
use App\Application\Service\CupEvent\Exception\CupEventNotFound;
use App\Application\Service\CupEvent\UpdateCupEvent;
use App\Application\Service\CupEvent\UpdateCupEventService;
use App\Application\Service\Event\Exception\EventNotFound;
use App\Domain\Auth\Impression;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Cup\CupEvent\CupEventUpdateInput;
use App\Domain\Cup\CupEvent\CupEventUpdater;
use App\Domain\Cup\CupEvent\Exception\CupAlreadyContainsEvent;
use App\Domain\Event\Exception\EventNotExists;
use App\Domain\Shared\DummyTransactional;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class UpdateCupEventServiceTest extends TestCase
{
    private CupEventRepository&MockObject $cupEvents;

    private CupEventUpdater&MockObject $updater;

    private UpdateCupEventService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new UpdateCupEventService(
            $this->cupEvents = $this->createMock(CupEventRepository::class),
            $this->updater = $this->createMock(CupEventUpdater::class),
            new CupEventAssembler(new AuthAssembler()),
            new DummyTransactional(),
        );
    }

    #[Test]
    public function it_fails_when_the_stage_is_not_found(): void
    {
        $this->expectException(CupEventNotFound::class);
        $this->cupEvents->expects($this->once())->method('lockById')->with(3)->willReturn(null);
        $this->updater->expects($this->never())->method('update');
        $this->cupEvents->expects($this->never())->method('update');

        $this->service->execute($this->command());
    }

    #[Test]
    public function it_maps_a_missing_event_from_the_updater(): void
    {
        $cupEvent = $this->cupEvent();
        $this->cupEvents->method('lockById')->willReturn($cupEvent);
        $this->cupEvents->expects($this->never())->method('update');
        $this->updater
            ->expects($this->once())
            ->method('update')
            ->with($cupEvent, new CupEventUpdateInput(2, 75.0, 1))
            ->willThrowException(new EventNotExists())
        ;

        $this->expectException(EventNotFound::class);
        $this->service->execute($this->command());
    }

    #[Test]
    public function it_maps_a_duplicate_stage_from_the_updater(): void
    {
        $cupEvent = $this->cupEvent();
        $this->cupEvents->method('lockById')->willReturn($cupEvent);
        $this->cupEvents->expects($this->never())->method('update');
        $this->updater->method('update')->willThrowException(new CupAlreadyContainsEvent());

        $this->expectException(CupEventAlreadyExists::class);
        $this->service->execute($this->command());
    }

    #[Test]
    public function it_updates_persists_and_assembles_the_stage(): void
    {
        $cupEvent = $this->cupEvent();
        $this->cupEvents->expects($this->once())->method('lockById')->with(3)->willReturn($cupEvent);
        $this->updater
            ->expects($this->once())
            ->method('update')
            ->with($cupEvent, new CupEventUpdateInput(2, 75.0, 1))
            ->willReturn($cupEvent)
        ;
        $this->cupEvents->expects($this->once())->method('update')->with($cupEvent);

        $view = $this->service->execute($this->command());

        $this->assertSame('3', $view->id);
        $this->assertSame('1', $view->cupId);
        $this->assertSame('2', $view->eventId);
        $this->assertSame('75', $view->points);
    }

    private function command(): UpdateCupEvent
    {
        $dto = new CupEventDto();
        $dto->eventId = 2;
        $dto->points = 75;

        return new UpdateCupEvent('3', $dto, new UserId(1));
    }

    private function cupEvent(): CupEvent
    {
        $cupEvent = $this->createMock(CupEvent::class);
        $cupEvent->method('__get')->willReturnMap([
            ['id', 3],
            ['cup_id', 1],
            ['event_id', 2],
            ['points', 75.0],
            ['created', new Impression(Carbon::parse('2026-01-01'), 1)],
            ['updated', new Impression(Carbon::parse('2026-01-02'), 1)],
        ]);

        return $cupEvent;
    }
}
