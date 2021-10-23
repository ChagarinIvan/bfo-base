<?php

namespace App\Models\Cups;

use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Group;
use App\Models\ProtocolLine;
use Illuminate\Support\Collection;

class MasterCupType extends AbstractCupType
{
    public function getId(): string
    {
        return CupType::MASTER;
    }

    public function getNameKey(): string
    {
        return 'app.cup.type.master';
    }

    /**
     * @param CupEvent $cupEvent
     * @param Group $mainGroup
     * @return Collection //array<int, CupEventPoint>
     */
    public function calculateEvent(CupEvent $cupEvent, Group $mainGroup): Collection
    {
        $results = new Collection();
        $cupEventProtocolLines = $this->getGroupProtocolLines($cupEvent, $mainGroup);
        $eventGroupsId = $this->getGroups()->pluck('id');

        $groupsName = $mainGroup->maleGroups();
        $eventDistances = $this->distanceService->getCupEventDistancesByGroups($cupEvent, $eventGroupsId, $groupsName)
            ->pluck('id')
            ->toArray();

        $cupEventProtocolLines = $cupEventProtocolLines->filter(
            fn (ProtocolLine $protocolLine) => in_array($protocolLine->distance_id, $eventDistances, true)
        );

        $cupEventProtocolLines = $cupEventProtocolLines->groupBy('distance.group_id');
        $validGroups = $eventGroupsId->flip();
        $cupEventProtocolLines = $cupEventProtocolLines->intersectByKeys($validGroups);
        $groups = $this->groupsService->getCupEventGroups($cupEvent);
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
        return $this->calculateLines(
            $cupEvent,
            $this->protocolLinesRepository->getCupEventGroupProtocolLinesForPersonsWithPayment($cupEvent, $groupId),
        );
    }

    protected function getGroupProtocolLines(CupEvent $cupEvent, Group $group): Collection
    {
        $year = $cupEvent->cup->year;
        $startYear = $year - $group->years();
        $finishYear = $startYear - 5;

        return $this->protocolLinesRepository->getCupEventProtocolLinesForPersonsCertainAge(
            $cupEvent,
            $finishYear,
            $startYear,
            true
        );
    }

    public function getGroups(): Collection
    {
        return $this->groupsService->getGroups([
            Group::M35,
            Group::M40,
            Group::M45,
            Group::M50,
            Group::M55,
            Group::M60,
            Group::M65,
            Group::M70,
            Group::M75,
            Group::M80,
            Group::W35,
            Group::W40,
            Group::W45,
            Group::W50,
            Group::W55,
            Group::W60,
            Group::W65,
            Group::W70,
            Group::W75,
            Group::W80,
        ]);
    }
}
