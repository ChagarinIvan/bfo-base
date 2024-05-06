<?php

declare(strict_types=1);

namespace Tests\Application\Service\CupEvent;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\CupEvent\CupEventAssembler;
use App\Application\Dto\CupEvent\CupEventSearchDto;
use App\Application\Dto\CupEvent\ViewCupEventDto;
use App\Application\Dto\Event\EventAssembler;
use App\Application\Service\CupEvent\ListCupEvent;
use App\Application\Service\CupEvent\ListCupEventService;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupRepository;
use App\Domain\CupEvent\CupEvent;
use App\Domain\CupEvent\CupEventRepository;
use App\Domain\Event\Event;
use App\Domain\Shared\Criteria;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ListCupEventServiceTest extends TestCase
{
    private ListCupEventService $service;

    private CupEventRepository&MockObject $cupEvents;

    private CupRepository&MockObject $cups;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ListCupEventService(
            $this->cups = $this->createMock(CupRepository::class),
            $this->cupEvents = $this->createMock(CupEventRepository::class),
            new CupEventAssembler(new EventAssembler(new AuthAssembler), new AuthAssembler)
        );
    }

    /** @test */
    public function it_gets_list_of_cup_events(): void
    {
        /** @var CupEvent[] $cupEvents */
        $cupEvents = CupEvent::factory(count: 2)->make();
        /** @var Event $event */
        $event = Event::factory()->makeOne();

        foreach ($cupEvents as $cupEvent) {
            $cupEvent->event = $event;
        }

        /** @var Cup $cup */
        $cup = Cup::factory()->makeOne();

        $this->cupEvents
            ->expects($this->once())
            ->method('byCriteria')
            ->with($this->equalTo(new Criteria(['cupId' => 1])))
            ->willReturn($cupEvents)
        ;

        $this->cups
            ->expects($this->exactly(2))
            ->method('byId')
            ->willReturn($cup)
        ;

        $dto = new CupEventSearchDto('1');

        $command = new ListCupEvent($dto);
        $result = $this->service->execute($command);

        $this->assertIsList($result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(ViewCupEventDto::class, $result);
        $this->assertEquals($cupEvents[1]->id, $result[1]->id);
    }
}
