<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Distance\Distance;
use App\Domain\Distance\DistanceRepository;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Shared\Criteria;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final readonly class UniteEventDataService
{
    public function __construct(private DistanceRepository $distances)
    {
    }

    /** @param Collection<int, Event> $events */
    public function execute(Collection $events, Event $newEvent): void
    {
        /** @var Event $firstEvent */
        $firstEvent = $events->first();
        $newProtocolLines = new Collection;
        $firstEventProtocolLines = $firstEvent->protocolLines->groupBy('distance.group_id');

        foreach ($events->slice(1) as $event) {
            $eventProtocolLines = $event->protocolLines->groupBy('distance.group_id');
            $groupIds = $firstEventProtocolLines->keys()->merge($eventProtocolLines->keys())->unique();

            foreach ($groupIds as $groupId) {
                $firstDistance = $this->eventGroupDistance($firstEvent, $groupId);
                $eventDistance = $this->eventGroupDistance($event, $groupId);
                $distance = $this->eventGroupDistance($newEvent, $groupId) ?? new Distance;
                if ($firstDistance instanceof Distance) {
                    $distance->points += $firstDistance->points;
                    $distance->length += $firstDistance->length;
                }
                if ($eventDistance instanceof Distance) {
                    $distance->points += $eventDistance->points;
                    $distance->length += $eventDistance->length;
                }
                $distance->group_id = $groupId;
                $distance->event_id = $newEvent->id;
                $distance->save();

                $firstLines = $firstEventProtocolLines->get($groupId, collect())->keyBy('person_id');
                $eventLines = $eventProtocolLines->get($groupId, collect())->keyBy('person_id');
                foreach ($firstLines->keys()->merge($eventLines->keys())->unique() as $personId) {
                    $firstLine = $firstLines->get($personId);
                    $eventLine = $eventLines->get($personId);
                    if ($firstLine instanceof ProtocolLine) {
                        $line = $firstLine->replicate();
                        if ($eventLine instanceof ProtocolLine && $firstLine->time instanceof Carbon && $eventLine->time instanceof Carbon) {
                            $line->time = $line->time->addHours($eventLine->time->hour)->addMinutes($eventLine->time->minute)->addSeconds($eventLine->time->second);
                        } else {
                            $line->time = null;
                        }
                    } elseif ($eventLine instanceof ProtocolLine) {
                        $line = $eventLine->replicate();
                        $line->time = null;
                    } else {
                        continue;
                    }
                    $line->distance_id = $distance->id;
                    $newProtocolLines->push($line);
                }
            }
            $firstEventProtocolLines = $newProtocolLines->groupBy('distance.group_id');
            $newProtocolLines = new Collection;
            $firstEvent = $newEvent;
        }

        $number = 1;
        foreach ($firstEventProtocolLines as $groupProtocolLines) {
            foreach ($groupProtocolLines as $line) {
                $line->runner_number = $number++;
                $line->time = $line->time === null ? null : Carbon::createFromFormat('H:i:s', $line->time->format('H:i:s'));
            }
            $place = 1;
            foreach ($groupProtocolLines->sortBy(static fn (ProtocolLine $line): int => $line->time ? $line->time->secondsSinceMidnight() : 86400) as $line) {
                $line->place = $line->time === null ? $place : $place++;
                $line->points = null;
                new ProtocolLine($line->toArray())->save();
            }
        }
    }

    private function eventGroupDistance(Event $event, int $groupId): ?Distance
    {
        return $this->distances->oneByCriteria(new Criteria([
            'eventId' => $event->id,
            'groupId' => $groupId,
        ]));
    }
}
