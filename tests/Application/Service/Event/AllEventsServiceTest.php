<?php

declare(strict_types=1);

namespace Tests\Application\Service\Event;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\SearchEventDto;
use App\Application\Service\Event\AllEvents;
use App\Application\Service\Event\AllEventsService;
use App\Domain\Auth\Impression;
use App\Domain\Event\Event;
use App\Domain\Event\EventRepository;
use App\Domain\Event\EventResources;
use App\Domain\Shared\Criteria;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class AllEventsServiceTest extends TestCase
{
    #[Test]
    public function it_reads_all_events_without_pagination_and_maps_resources(): void
    {
        $event = new Event;
        $event->id = 7;
        $event->competition_id = 3;
        $event->name = 'Stage';
        $event->description = 'Description';
        $event->date = Carbon::parse('2026-05-10');
        $event->created = new Impression(Carbon::parse('2026-05-01'), 1);
        $event->updated = new Impression(Carbon::parse('2026-05-01'), 1);
        $event->setAttribute('protocol_lines_count', 4);

        $events = $this->createMock(EventRepository::class);
        $events
            ->expects($this->once())
            ->method('byCriteria')
            ->with(
                new Criteria(['competitionId' => '3']),
                new EventResources(withCompetitionName: true),
            )
            ->willReturn(new Collection([$event]))
        ;

        $result = (new AllEventsService(
            $events,
            new EventAssembler(new AuthAssembler),
        ))->execute(new AllEvents(new SearchEventDto(
            competitionId: '3',
            withCompetition: '1',
        )));

        $this->assertCount(1, $result);
        $this->assertSame('7', $result[0]->id);
        $this->assertSame(4, $result[0]->participantsCount);
    }
}
