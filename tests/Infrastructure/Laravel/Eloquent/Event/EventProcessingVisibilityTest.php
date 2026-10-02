<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Laravel\Eloquent\Event;

use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Distance\DistanceRepository;
use App\Domain\Event\Event;
use App\Domain\Event\EventProcessingStatus;
use App\Domain\Event\EventRepository;
use App\Domain\Event\EventResources;
use App\Domain\Group\Group;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Shared\Criteria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function array_map;

final class EventProcessingVisibilityTest extends TestCase
{
    use RefreshDatabase;

    /** @return iterable<string, array{EventProcessingStatus, bool}> */
    public static function hiddenEvents(): iterable
    {
        foreach (EventProcessingStatus::cases() as $status) {
            if ($status !== EventProcessingStatus::READY) {
                yield $status->value => [$status, true];
            }
        }
        yield 'inactive ready event' => [EventProcessingStatus::READY, false];
    }

    #[Test]
    #[DataProvider('hiddenEvents')]
    public function guest_event_reads_and_result_reads_hide_unready_or_inactive_data(
        EventProcessingStatus $status,
        bool $active,
    ): void {
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne(['active' => true]);
        /** @var Group $group */
        $group = Group::factory()->createOne();
        [$ready, $readyDistance, $readyLine] = $this->results($competition, $group, EventProcessingStatus::READY, true);
        [$hidden, $hiddenDistance, $hiddenLine] = $this->results($competition, $group, $status, $active);
        $events = $this->app->make(EventRepository::class);
        $distances = $this->app->make(DistanceRepository::class);
        $lines = $this->app->make(ProtocolLineRepository::class);

        $guestResources = new EventResources(readyOnly: true);
        $visibleEventIds = $active ? [$ready->id, $hidden->id] : [$ready->id];
        $this->assertSame($active ? $hidden->id : null, $events->byId($hidden->id)?->id);
        $this->assertNull($events->byId($hidden->id, $guestResources));
        $this->assertSame($ready->id, $events->byId($ready->id)?->id);
        $this->assertSame($active ? $hidden->id : null, $events->oneByCriteria(new Criteria(['ids' => [$hidden->id]]))?->id);
        $this->assertSame($visibleEventIds, $events->byCriteria(Criteria::empty())->pluck('id')->all());
        $this->assertSame([$ready->id], $events->byCriteria(Criteria::empty(), $guestResources)->pluck('id')->all());
        $this->assertSame($visibleEventIds, array_map(static fn (Event $event): int => $event->id, $events->paginate(Criteria::empty())->items()));
        $this->assertSame([$ready->id], array_map(static fn (Event $event): int => $event->id, $events->paginate(Criteria::empty(), $guestResources)->items()));

        $this->assertNull($distances->oneByCriteria(new Criteria(['id' => $hiddenDistance->id])));
        $this->assertSame([$readyDistance->id], $distances->byCriteria(Criteria::empty())->pluck('id')->all());

        $this->assertNull($lines->byId($hiddenLine->id));
        $this->assertSame($readyLine->id, $lines->byId($readyLine->id)?->id);
        $this->assertNull($lines->oneByCriteria(new Criteria(['eventId' => $hidden->id])));
        $this->assertSame([$readyLine->id], $lines->byCriteria(Criteria::empty())->pluck('id')->all());
        $this->assertSame([$readyLine->id], $lines->byCriteria(new Criteria(['completedRank' => false]))->pluck('id')->all());
        $this->assertSame([$readyLine->id], array_map(static fn (ProtocolLine $line): int => $line->id, $lines->paginate(Criteria::empty())->items()));

        if ($active) {
            $this->assertSame($hidden->id, $events->lockById($hidden->id)?->id);
            $this->assertSame([$hidden->id], $events->lockByCriteria(new Criteria(['ids' => [$hidden->id]]))->pluck('id')->all());
            $this->assertSame($hiddenDistance->id, $distances->lockOneByCriteria(new Criteria(['id' => $hiddenDistance->id]))?->id);
            $this->assertSame($hiddenLine->id, $lines->lockById($hiddenLine->id)?->id);
            $this->assertSame($hiddenLine->id, $lines->lockOneByCriteria(new Criteria(['eventId' => $hidden->id]))?->id);
            $this->assertSame([$hiddenLine->id], $lines->lockByCriteria(new Criteria(['eventId' => $hidden->id]))->pluck('id')->all());
        } else {
            $this->assertNull($events->lockById($hidden->id));
            $this->assertNull($distances->lockOneByCriteria(new Criteria(['id' => $hiddenDistance->id])));
            $this->assertNull($lines->lockById($hiddenLine->id));
        }
    }

    /** @return array{Event, Distance, ProtocolLine} */
    private function results(Competition $competition, Group $group, EventProcessingStatus $status, bool $active): array
    {
        /** @var Event $event */
        $event = Event::factory()->createOne([
            'competition_id' => $competition->id,
            'processing_status' => $status,
            'date' => '2026-10-02',
            'active' => $active,
        ]);
        /** @var Distance $distance */
        $distance = Distance::factory()->createOne(['id' => null, 'event_id' => $event->id, 'group_id' => $group->id]);
        /** @var ProtocolLine $line */
        $line = ProtocolLine::factory()->createOne(['distance_id' => $distance->id, 'complete_rank' => '']);

        return [$event, $distance, $line];
    }
}
