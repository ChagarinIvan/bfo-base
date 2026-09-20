<?php

declare(strict_types=1);

namespace Tests\Application\Service\CupEvent;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\CupEvent\CreateCupEventDto;
use App\Application\Dto\CupEvent\CupEventAssembler;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Application\Service\CupEvent\AddCupEvent;
use App\Application\Service\CupEvent\AddCupEventService;
use App\Application\Service\CupEvent\Exception\CupEventAlreadyExists;
use App\Application\Service\Event\Exception\EventNotFound;
use App\Domain\Auth\Impression;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Cup\CupEvent\Exception\CupAlreadyContainsEvent;
use App\Domain\Cup\CupEvent\Factory\CupEventFactory;
use App\Domain\Cup\CupEvent\Factory\CupEventInput;
use App\Domain\Cup\Exception\CupNotExists;
use App\Domain\Event\Exception\EventNotExists;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class AddCupEventServiceTest extends TestCase
{
    private CupEventFactory&MockObject $factory;

    private AddCupEventService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AddCupEventService(
            $this->factory = $this->createMock(CupEventFactory::class),
            $this->createStub(CupEventRepository::class),
            new CupEventAssembler(new AuthAssembler()),
        );
    }

    #[Test]
    public function it_maps_a_missing_cup_from_the_factory_to_an_application_error(): void
    {
        $this->expectException(CupNotFound::class);
        $this->factory->method('create')->willThrowException(new CupNotExists());

        $this->service->execute($this->command());
    }

    #[Test]
    public function it_maps_a_missing_event_from_the_factory_to_an_application_error(): void
    {
        $this->expectException(EventNotFound::class);
        $this->factory->method('create')->willThrowException(new EventNotExists());

        $this->service->execute($this->command());
    }

    #[Test]
    public function it_maps_a_duplicate_stage_from_the_factory_to_an_application_error(): void
    {
        $this->expectException(CupEventAlreadyExists::class);
        $this->factory->method('create')->willThrowException(new CupAlreadyContainsEvent());

        $this->service->execute($this->command());
    }

    #[Test]
    public function it_creates_and_persists_a_stage_from_the_validated_factory(): void
    {
        $cupEvent = $this->createMock(CupEvent::class);
        $cupEvent->expects($this->atLeast(6))->method('__get')->willReturnMap([
            ['id', 3],
            ['cup_id', 1],
            ['event_id', 2],
            ['points', 100.0],
            ['created', new Impression(Carbon::parse('2026-01-01'), 1)],
            ['updated', new Impression(Carbon::parse('2026-01-01'), 1)],
        ]);
        $input = new CupEventInput(1, 2, 100.0, 1);

        $this->factory
            ->expects($this->once())
            ->method('create')
            ->with($input)
            ->willReturn($cupEvent)
        ;

        $cupEvents = $this->createMock(CupEventRepository::class);
        $cupEvents->expects($this->once())->method('add')->with($cupEvent);
        $this->service = new AddCupEventService(
            $this->factory,
            $cupEvents,
            new CupEventAssembler(new AuthAssembler()),
        );

        $view = $this->service->execute($this->command());

        $this->assertSame('3', $view->id);
        $this->assertSame('1', $view->cupId);
        $this->assertSame('2', $view->eventId);
        $this->assertSame('100', $view->points);
    }

    private function command(): AddCupEvent
    {
        $dto = new CreateCupEventDto();
        $dto->cupId = 1;
        $dto->eventId = 2;
        $dto->points = 100;

        return new AddCupEvent($dto, new UserId(1));
    }
}
