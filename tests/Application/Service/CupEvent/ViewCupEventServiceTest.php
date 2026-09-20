<?php

declare(strict_types=1);

namespace Tests\Application\Service\CupEvent;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\CupEvent\CupEventAssembler;
use App\Application\Dto\CupEvent\ViewCupEventDto;
use App\Application\Service\CupEvent\Exception\CupEventNotFound;
use App\Application\Service\CupEvent\ViewCupEvent;
use App\Application\Service\CupEvent\ViewCupEventService;
use App\Domain\Auth\Impression;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupEvent\CupEventRepository;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ViewCupEventServiceTest extends TestCase
{
    private CupEventRepository&MockObject $cupEvents;

    private ViewCupEventService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ViewCupEventService(
            $this->cupEvents = $this->createMock(CupEventRepository::class),
            new CupEventAssembler(new AuthAssembler()),
        );
    }

    #[Test]
    public function it_fails_when_the_stage_is_not_found(): void
    {
        $this->expectException(CupEventNotFound::class);
        $this->cupEvents->expects($this->once())->method('byId')->with(3)->willReturn(null);

        $this->service->execute(new ViewCupEvent('3'));
    }

    #[Test]
    public function it_assembles_the_found_stage(): void
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
        $this->cupEvents->expects($this->once())->method('byId')->with(3)->willReturn($cupEvent);

        $view = $this->service->execute(new ViewCupEvent('3'));

        $this->assertInstanceOf(ViewCupEventDto::class, $view);
        $this->assertSame('3', $view->id);
        $this->assertSame('1', $view->cupId);
        $this->assertSame('2', $view->eventId);
        $this->assertSame('75', $view->points);
    }
}
