<?php

declare(strict_types=1);

namespace Tests\Application\Service\Event;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\UniteEventsDto;
use App\Application\Service\Event\UniteEvents;
use App\Application\Service\Event\UniteEventsService;
use App\Domain\Auth\Impression;
use App\Domain\Distance\DistanceRepository;
use App\Domain\Event\Event;
use App\Domain\Event\EventRepository;
use App\Domain\Event\EventResources;
use App\Domain\Event\Factory\EventFactory;
use App\Domain\Event\Factory\UniteFactory;
use App\Domain\Event\UniteEventDataService;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\FrozenClock;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UniteEventsServiceTest extends TestCase
{
    #[Test]
    public function it_locks_inputs_and_delegates_creation_to_factory(): void
    {
        $first = $this->sourceEvent(1, 'First');
        $second = $this->sourceEvent(2, 'Second');
        $events = $this->createMock(EventRepository::class);
        $events->expects($this->once())
            ->method('lockByCriteria')
            ->with(
                new Criteria(['competitionId' => 9, 'ids' => [1, 2]]),
                new EventResources(withDistances: true, withProtocolLines: true),
            )
            ->willReturn(new Collection([$first, $second]));

        $newEvent = new Event;
        $newEvent->name = 'First + Second';
        $newEvent->description = "Аб'яднанне этапаў: First + Second";
        $newEvent->competition_id = 9;
        $newEvent->date = Carbon::parse('2026-05-10');
        $newEvent->created = new Impression(Carbon::parse('2026-05-11'), 4);
        $newEvent->updated = $newEvent->created;
        $newEventFactory = $this->createMock(EventFactory::class);
        $newEventFactory->method('create')->willReturn($newEvent);
        $events->expects($this->once())->method('add')->with($newEvent);

        $service = new UniteEventsService(
            $events,
            new UniteFactory($newEventFactory),
            new UniteEventDataService($this->createStub(DistanceRepository::class)),
            new FrozenClock(Carbon::parse('2026-05-11')),
            new EventAssembler(new AuthAssembler),
            new DummyTransactional,
        );

        $input = new UniteEventsDto;
        $input->eventIds = [1, 2];
        $result = $service->execute(new UniteEvents(9, $input, new UserId(4)));

        $this->assertSame('First + Second', $result->name);
    }

    private function sourceEvent(int $id, string $name): Event
    {
        $event = new Event;
        $event->id = $id;
        $event->name = $name;
        $event->competition_id = 9;
        $event->date = Carbon::parse('2026-05-10');
        $event->setRelation('protocolLines', new Collection);

        return $event;
    }
}
