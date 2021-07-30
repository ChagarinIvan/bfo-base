<?php

namespace App\Models\Cups;

use App\Models\Cup;
use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Distance;
use App\Models\Group;
use App\Models\Person;
use App\Models\ProtocolLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MasterCupType implements CupTypeInterface
{
    /** @var Collection */
    private $eventGroupsIds;

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
                return ($a->points != '-' ? $a->points : 0) > ($b->points != '-' ? $b->points : 0) ? -1 : 1;
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

    private function getEventGroups(CupEvent $cupEvent): Collection
    {
        if (!$this->eventGroupsIds) {
            $this->eventGroupsIds = $cupEvent->cup->groups->pluck('id');
        }
        return $this->eventGroupsIds;
    }

    /**
     * @param CupEvent $cupEvent
     * @param Group $mainGroup
     * @return array<int, CupEventPoint>|Collection
     */
    public function calculateEvent(CupEvent $cupEvent, Group $mainGroup): Collection
    {
        $results = new Collection();
        $cupEventParticipants = $this->getGroupPersonsIds($cupEvent, $mainGroup);
        $eventGroups = $this->getEventGroups($cupEvent);

        $cupEventProtocolLines = $cupEvent->event->protocolLines()
            ->with(['distance'])
            ->whereIn('person_id', $cupEventParticipants->pluck('id'))
            ->get();

        $groupsName = $mainGroup->maleGroups();
        $eventDistances = Distance::selectRaw(DB::raw('distances.id'))
            ->join('groups', 'groups.id', '=', 'distances.group_id')
            ->whereIn('group_id', $eventGroups)
            ->whereIn('groups.name', $groupsName)
            ->whereEventId($cupEvent->event_id)
            ->get()
            ->pluck('id')
            ->toArray();

        $cupEventProtocolLines = $cupEventProtocolLines->filter(function (ProtocolLine $protocolLine) use ($eventDistances) {
            return in_array($protocolLine->distance_id, $eventDistances, true);
        });

        $cupEventProtocolLines = $cupEventProtocolLines->groupBy('distance.group_id');
        $validGroups = $eventGroups->flip();
        $cupEventProtocolLines = $cupEventProtocolLines->intersectByKeys($validGroups);
        $hasGroupOnEvent = $cupEvent->event->groups()->pluck('id')->flip()->has($mainGroup->id);

        foreach ($cupEventProtocolLines as $groupId => $groupProtocolLines) {
            $needDivideGroup = !$hasGroupOnEvent && $mainGroup->isPreviousGroup($groupId);
            if ($needDivideGroup) {
                $eventGroupResults = $this->calculateLines($cupEvent, $groupProtocolLines);
            } else {
                $eventGroupResults = $this->calculateGroup($cupEvent, $groupId);
            }
            $results = $results->merge($eventGroupResults->intersectByKeys($groupProtocolLines->keyBy('person_id')));
        }

        return $results->sortByDesc(function (CupEventPoint $cupEventResult) {
            return $cupEventResult->points;
        });
    }

    /**
     * @param CupEvent $cupEvent
     * @param int $groupId
     * @return Collection
     */
    public function calculateGroup(CupEvent $cupEvent, int $groupId): Collection
    {
        $protocolLinesIds = ProtocolLine::selectRaw(DB::raw('protocol_lines.id AS id, persons_payments.date AS date'))
            ->join('person', 'person.id', '=', 'protocol_lines.person_id')
            ->join('persons_payments', 'person.id', '=', 'persons_payments.person_id')
            ->join('distances', 'distances.id', '=', 'protocol_lines.distance_id')
            ->where('persons_payments.year', $cupEvent->cup->year)
            ->where('distances.event_id', $cupEvent->event_id)
            ->where('distances.group_id', $groupId)
            ->havingRaw(DB::raw("persons_payments.date <= '{$cupEvent->event->date}'"))
            ->get();

        $protocolLines = ProtocolLine::find($protocolLinesIds->pluck('id'));
        return $this->calculateLines($cupEvent, $protocolLines);
    }

    /**
     * @param CupEvent $cupEvent
     * @param Collection $protocolLines
     * @return Collection
     */
    public function calculateLines(CupEvent $cupEvent, Collection $protocolLines): Collection
    {
        $cupEventPointsList = Collection::make();
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
                    $cupEventPoints = new CupEventPoint(
                        $cupEvent->id,
                        $protocolLine,
                        $firstResult->time === null ? 0 : $maxPoints
                    );
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

    private function getGroupPersonsIds(CupEvent $cupEvent, Group $group): Collection
    {
        $year = $cupEvent->cup->year;
        $startYear = $year - $group->years();
        $finishYear = $startYear - 5;

        return Person::selectRaw(DB::raw('person.id AS id, persons_payments.date AS date'))
            ->join('persons_payments', 'person.id', '=', 'persons_payments.person_id')
            ->where('person.birthday', '<=', "{$startYear}-01-01")
            ->where('person.birthday', '>', "{$finishYear}-01-01")
            ->where('persons_payments.year', '=', $year)
            ->havingRaw(DB::raw("persons_payments.date <= '{$cupEvent->event->date}'"))
            ->get();
    }
}
