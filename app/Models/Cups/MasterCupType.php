<?php

namespace App\Models\Cups;

use App\Models\Cup;
use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Group;
use App\Models\ProtocolLine;
use Illuminate\Support\Collection;

class MasterCupType implements CupTypeInterface
{
    public function getId(): string
    {
        return CupType::MASTER;
    }

    public function getName(): string
    {
        return 'Master';
    }

    public function calculate(Cup $cup, Collection $cupEvents, Collection $protocolLines, int $mainGroupId): array
    {
        $groupedByEventProtocolLines = $protocolLines->groupBy('event_id');
        $protocolLinesByIds = $protocolLines->keyBy('id');

        $results = [];
        foreach ($cupEvents as $event) {
            $eventProtocolLines = $groupedByEventProtocolLines->get($event->event_id);
            $eventResults = Collection::make();

            if ($eventProtocolLines !== null && !$eventProtocolLines->isEmpty()) {
                $groupProtocolLines = $eventProtocolLines->groupBy('group_id');
                foreach ($groupProtocolLines as $groupId => $groupEventProtocolLines) {
                    if ($groupId === $mainGroupId) {
                        $eventResults = $eventResults->merge($this->calculateEvent($event, $groupEventProtocolLines));
                    } else {
                        $groupLines = ProtocolLine::whereGroupId($groupId)
                            ->whereNotNull('person_id')
                            ->whereEventId($event->event_id)
                            ->get();

                        $groupLines = $groupLines->sortByDesc(function (ProtocolLine $line) {
                            return $line->time ? $line->time->diffInSeconds() : 0;
                        });

                        $groupEventProtocolLines->push($groupLines->first());
                        $groupEventProtocolLines->unique();
                        $eventResults = $eventResults->merge($this->calculateEvent($event, $groupEventProtocolLines));
                    }
                }
            }

            foreach ($eventResults as $result) {
                if ($protocolLinesByIds->has($result->protocolLineId)) {
                    /** @var ProtocolLine $protocolLine */
                    $protocolLine = $protocolLinesByIds->get($result->protocolLineId);
                    $results[$protocolLine->person_id][$event->id] = $result;
                }
            }
        }

        foreach ($results as &$cupEventPoints) {
            uasort($cupEventPoints, function (CupEventPoint $a, CupEventPoint $b) {
                if ($a->points === $b->points) {
                    return 0;
                }
                return ($a->points !== '-' ? $a->points : 0) > ($b->points !== '-' ? $b->points : 0) ? -1 : 1;
            });
        }

        uasort($results, function (array $person1Results, array $person2Results) use ($cup) {
            $person1Points = 0;
            foreach (array_slice($person1Results, 0, $cup->events_count) as $cupEventPoints) {
                /** @var CupEventPoint $cupEventPoints */
                $person1Points += $cupEventPoints->points;
            }
            $person2Points = 0;
            foreach (array_slice($person2Results, 0, $cup->events_count) as $cupEventPoints) {
                /** @var CupEventPoint $cupEventPoints */
                $person2Points += $cupEventPoints->points;
            }
            if ($person1Points === $person2Points) {
                return 0;
            }
            return $person1Points > $person2Points ? -1 : 1;
        });

        return $results;
    }

    public function getProtocolLines(Cup $cup, Group $mainGroup): Collection
    {
        return ProtocolLine::with('person')
            ->whereIn('event_id', $cup->events->pluck('event_id'))
            ->whereNotNull('person_id')
            ->whereGroupId($mainGroup->id)
            ->get();
    }

    /**
     * @param CupEvent $cupEvent
     * @param Collection $protocolLines
     * @return array<int, CupEventPoint>|Collection
     */
    public function calculateEvent(CupEvent $cupEvent, Collection $protocolLines): Collection
    {
        $cupEventPointsList = Collection::make();

        if ($protocolLines->isEmpty()) {
            return $cupEventPointsList;
        }

        $maxPoints = $cupEvent->points;

        $protocolLines = $protocolLines->sortByDesc(function (ProtocolLine $line) {
            return $line->time ? $line->time->diffInSeconds() : 0;
        });

        $first = true;
        //а этапах Кубков Федерации очки начисляются по формуле:
        //O = Kus × (2W ÷ T − 1),
        //где T – результат спортсмена в секундах, W – результат победителя в секундах, Kus – коэффициент уровня соревнований.

        foreach ($protocolLines as $protocolLine) {
            /** @var ProtocolLine $protocolLine */
            if ($first) {
                if ($protocolLine->person_id !== null) {
                    /** @var ProtocolLine $firstResult */
                    $firstResult = $protocolLines->first();
                    $firstResultSeconds = $firstResult->time ? $firstResult->time->secondsSinceMidnight() : 0;
                    $cupEventPoints = new CupEventPoint($cupEvent->id, $protocolLine->id, $maxPoints);
                    $first = false;
                } else {
                    $cupEventPoints = new CupEventPoint($cupEvent->id, $protocolLine->id, '-');
                }
            } else {
                if ($protocolLine->person_id === null) {
                    $points = '-';
                } elseif ($protocolLine->time !== null) {
                    $diff = $protocolLine->time->secondsSinceMidnight();
                    $points = (int)round($maxPoints * (2 * $firstResultSeconds / $diff - 1));
                    $points = $points < 0 ? 0 : $points;
                } else {
                    $points = 0;
                }
                $cupEventPoints = new CupEventPoint($cupEvent->id, $protocolLine->id, $points);
            }

            $cupEventPointsList->put($cupEventPoints->protocolLineId, $cupEventPoints);
        }

        return $cupEventPointsList;
    }
}
