<?php

namespace App\Models\Cups;

use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Group\CupGroup;
use App\Models\Group\CupGroupFactory;
use App\Models\Group\GroupAge;
use App\Models\Group\GroupMale;
use App\Models\ProtocolLine;
use Illuminate\Support\Collection;

class MasterCupType extends AbstractCupType
{
    protected const GROUPS_MAP = [
        'M_35' => ['M35', 'М35'],
        'M_40' => ['M40', 'М40'],
        'M_45' => ['M45', 'М45'],
        'M_50' => ['M50', 'М50'],
        'M_55' => ['M55', 'М55'],
        'M_60' => ['M60', 'М60'],
        'M_65' => ['M65', 'М65'],
        'M_70' => ['M70', 'М70'],
        'M_75' => ['M75', 'М75'],
        'M_80' => ['M80', 'М80'],
        'W_35' => ['Ж35', 'W35'],
        'W_40' => ['Ж40', 'W40'],
        'W_45' => ['Ж45', 'W45'],
        'W_50' => ['Ж50', 'W50'],
        'W_55' => ['Ж55', 'W55'],
        'W_60' => ['Ж60', 'W60'],
        'W_65' => ['Ж65', 'W65'],
        'W_70' => ['Ж70', 'W70'],
        'W_75' => ['Ж75', 'W75'],
        'W_80' => ['Ж80', 'W80'],
    ];

    public function getId(): string
    {
        return CupType::MASTER;
    }

    public function getNameKey(): string
    {
        return 'app.cup.type.master';
    }

    public function calculateEvent(CupEvent $cupEvent, CupGroup $mainGroup): Collection
    {
        $results = new Collection();
        $cupEventProtocolLines = $this->getGroupProtocolLines($cupEvent, $mainGroup);
        $eventGroupsId = $this->getEventGroups($mainGroup->male())->pluck('id');

        $eventDistances = $this->distanceService
            ->getCupEventDistancesByGroups($cupEvent, $eventGroupsId)
            ->pluck('id')
            ->toArray()
        ;


        $cupEventProtocolLines = $cupEventProtocolLines->filter(
            fn (ProtocolLine $protocolLine) => in_array($protocolLine->distance_id, $eventDistances, true)
        );

        $cupEventProtocolLines = $cupEventProtocolLines->groupBy('distance.group_id');
        $validGroups = $eventGroupsId->flip();
        $cupEventProtocolLines = $cupEventProtocolLines->intersectByKeys($validGroups);
        $groups = $this->groupsService->getCupEventGroups($cupEvent);

        $count = $groups
            ->pluck('name')
            ->intersect(self::GROUPS_MAP[$mainGroup->id()])
            ->count()
        ;

        foreach ($cupEventProtocolLines as $groupId => $groupProtocolLines) {
            $group = $this->groupsService->getGroup($groupId);
            if (
                $count === 0
                && in_array($group->name, self::GROUPS_MAP[$mainGroup->prev()->id()], true)
            ) {
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
    protected function calculateGroup(CupEvent $cupEvent, int $groupId): Collection
    {
        return $this->calculateLines(
            $cupEvent,
            $this->protocolLinesRepository->getCupEventGroupProtocolLinesForPersonsWithPayment($cupEvent, $groupId),
        );
    }

    protected function getGroupProtocolLines(CupEvent $cupEvent, CupGroup $group): Collection
    {
        $year = $cupEvent->cup->year;
        $startYear = $year - $group->age() ?->value ?? 0;
        $finishYear = $startYear - 4;

        return $this->protocolLinesRepository->getCupEventProtocolLinesForPersonsCertainAge(
            $cupEvent,
            $finishYear,
            $startYear,
            true
        );
    }

    protected function getEventGroups(GroupMale $male): Collection
    {
        $groups = Collection::make();
        foreach ($this->getCalculatedGroups() as $cupGroup) {
            if ($cupGroup->male() === $male) {
                $groups = $groups->merge($this->groupsService->getGroups(static::GROUPS_MAP[$cupGroup->id()]));
            }
        }
        return $groups;
    }

    /**
     * @return Collection|CupGroup[]
     */
    public function getGroups(): Collection
    {
        return CupGroupFactory::getAgeTypeGroups([
            GroupAge::a35,
            GroupAge::a40,
            GroupAge::a45,
            GroupAge::a50,
            GroupAge::a55,
            GroupAge::a60,
            GroupAge::a65,
            GroupAge::a70,
            GroupAge::a75,
            GroupAge::a80,
        ]);
    }

    public function getCalculatedGroups(): Collection
    {
        return $this->getGroups();
    }
}
