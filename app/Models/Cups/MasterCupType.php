<?php

namespace App\Models\Cups;

use App\Models\Cup;
use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Group;
use App\Models\PersonPayment;
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

    public function calculate(Cup $cup, Collection $cupEvents, Group $mainGroup): array
    {
        $results = Collection::make();
        foreach ($cupEvents as $cupEvent) {
            $results = $results->merge($this->calculateEvent($cupEvent, $mainGroup));
        }

        $results = $results->groupBy('protocolLine.person_id');
        $results = $results->toArray();
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
     * @param Group $mainGroup
     * @return array<int, CupEventPoint>|Collection
     */
    public function calculateEvent(CupEvent $cupEvent, Group $mainGroup): Collection
    {
        $groupProtocolLines = ProtocolLine::whereEventId($cupEvent->event_id)
            ->whereGroupId($mainGroup->id)
            ->get();
        $emptyGroup = $groupProtocolLines->count() === 0;

        $emptyNextGroup = false;
        if (isset(Group::NEXT_GROUPS[$mainGroup->name])) {
            $nextGroupProtocolLines = ProtocolLine::whereEventId($cupEvent->event_id)
                ->whereGroupId(Group::NEXT_GROUPS[$mainGroup->name])
                ->get();
            $emptyNextGroup = $nextGroupProtocolLines->count() === 0;
        }

        $needSplit = $emptyGroup || $emptyNextGroup;

        if ($needSplit) {
            if ($emptyGroup) {
                if (isset(Group::CUP_GROUPS[$mainGroup->name])) {
                    $additionalGroups = Group::CUP_GROUPS[$mainGroup->name];
                    foreach ($additionalGroups as $groupName) {
                        $group = Group::whereName($groupName)->first();
                        $protocolLines = ProtocolLine::whereEventId($cupEvent->event_id)
                            ->whereGroupId($group->id)
                            ->get();

                        if ($protocolLines->count() > 0) {
                            break;
                        }
                    }
                } else {
                    return Collection::make();
                }
            } else {
                $group = $mainGroup;
            }

            return $this->calculateLines($cupEvent, ProtocolLine::with('person.payments')
                ->whereEventId($cupEvent->event_id)
                ->whereNotNull('person_id')
                ->whereIn('person_id', $cupEvent->getGroupPersons($mainGroup)->pluck('id'))
                ->whereGroupId($group->id)
                ->get()
            );
        } else {
            $cupEventPointsList = $this->calculateLines($cupEvent, ProtocolLine::with('person.payments')
                ->whereEventId($cupEvent->event_id)
                ->whereNotNull('person_id')
                ->whereGroupId($mainGroup->id)
                ->get()
            );
        }

        return $cupEventPointsList;
    }

    /**
     * @param CupEvent $cupEvent
     * @param Collection|ProtocolLine[] $lines
     * @return array<int, CupEventPoint>|Collection
     */
    public function calculateLines(CupEvent $cupEvent, Collection $lines): Collection
    {
        $cupEventPointsList = Collection::make();

        $protocolLines = Collection::make();
        foreach($lines as $protocolLine) {
            $payment = $protocolLine->person->payments->where('year', $cupEvent->cup->year)->first();
            if ($payment instanceof PersonPayment && $cupEvent->event->date >= $payment->date) {
                $protocolLines->push($protocolLine);
            }
        }

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
                    $cupEventPoints = new CupEventPoint($cupEvent->id, $protocolLine, $maxPoints);
                    $first = false;
                } else {
                    $cupEventPoints = new CupEventPoint($cupEvent->id, $protocolLine, '-');
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
                $cupEventPoints = new CupEventPoint($cupEvent->id, $protocolLine, $points);
            }

            $cupEventPointsList->put($cupEventPoints->protocolLine->person_id, $cupEventPoints);
        }

        return $cupEventPointsList;
    }
}
