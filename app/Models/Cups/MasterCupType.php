<?php

namespace App\Models\Cups;

use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Distance;
use App\Models\Group;
use App\Models\Person;
use App\Models\ProtocolLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MasterCupType extends AbstractCupType
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

        $cupEventProtocolLines = $cupEventProtocolLines->filter(
            fn (ProtocolLine $protocolLine) => in_array($protocolLine->distance_id, $eventDistances, true)
        );

        $cupEventProtocolLines = $cupEventProtocolLines->groupBy('distance.group_id');
        $validGroups = $eventGroups->flip();
        $cupEventProtocolLines = $cupEventProtocolLines->intersectByKeys($validGroups);
        $groups = Group::find(Distance::with(['group'])->whereEventId($cupEvent->event->id)->get()->pluck('group_id'));
        $hasGroupOnEvent = $groups->pluck('id')->flip()->has($mainGroup->id);

        foreach ($cupEventProtocolLines as $groupId => $groupProtocolLines) {
            $needDivideGroup = !$hasGroupOnEvent && $mainGroup->isPreviousGroup($groupId);
            if ($needDivideGroup) {
                $eventGroupResults = $this->calculateLines($cupEvent, $groupProtocolLines);
            } else {
                $eventGroupResults = $this->calculateGroup($cupEvent, $groupId);
            }
            $results = $results->merge($eventGroupResults->intersectByKeys($groupProtocolLines->keyBy('person_id')));
        }

        return $results->sortByDesc(fn (CupEventPoint $cupEventResult) => $cupEventResult->points);
    }

    /**
     * @param CupEvent $cupEvent
     * @param int $groupId
     * @return Collection
     */
    private function calculateGroup(CupEvent $cupEvent, int $groupId): Collection
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

        $ids = $protocolLinesIds->pluck('id');
        $protocolLines = ProtocolLine::whereIn('id', $ids)->get();
        return $this->calculateLines($cupEvent, $protocolLines);
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
