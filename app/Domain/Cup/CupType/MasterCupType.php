<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupType;

use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupEvent\CupEventPoint;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Cup\Group\CupGroupFactory;
use App\Domain\Cup\Group\GroupAge;
use App\Domain\Cup\Group\GroupMale;
use App\Domain\ProtocolLine\ProtocolLine;
use Illuminate\Support\Collection;
use function in_array;
use function PHPStan\dumpType;

class MasterCupType extends AbstractCupType
{
    protected const GROUPS_MAP = [
        'M_21_' => [],
        'M_35_' => ['M35', 'М35', 'МE(35)'],
        'M_40_' => ['M40', 'М40'],
        'M_45_' => ['M45', 'М45'],
        'M_50_' => ['M50', 'М50'],
        'M_55_' => ['M55', 'М55'],
        'M_60_' => ['M60', 'М60'],
        'M_65_' => ['M65', 'М65'],
        'M_70_' => ['M70', 'М70'],
        'M_75_' => ['M75', 'М75'],
        'M_80_' => ['M80', 'М80'],
        'W_21_' => [],
        'W_35_' => ['Ж35', 'W35', 'ЖE(35)'],
        'W_40_' => ['Ж40', 'W40'],
        'W_45_' => ['Ж45', 'W45'],
        'W_50_' => ['Ж50', 'W50'],
        'W_55_' => ['Ж55', 'W55'],
        'W_60_' => ['Ж60', 'W60'],
        'W_65_' => ['Ж65', 'W65'],
        'W_70_' => ['Ж70', 'W70'],
        'W_75_' => ['Ж75', 'W75'],
        'W_80_' => ['Ж80', 'W80'],
    ];

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
            static fn (ProtocolLine $protocolLine) => in_array($protocolLine->distance_id, $eventDistances, true)
        );

        $cupEventProtocolLines = $cupEventProtocolLines->groupBy('distance.group_id');
        $validGroups = $eventGroupsId->flip();
        /** @var Collection<string, mixed> $validGroups */
        $cupEventProtocolLines = $cupEventProtocolLines->intersectByKeys($validGroups);
        $groups = $this->groupsService->getCupEventGroups($cupEvent);

        $mainGroupExist = $groups
            ->pluck('name')
            ->intersect(self::GROUPS_MAP[$mainGroup->id()])
            ->count() > 0
        ;

        $previousGroupExist = $groups
            ->pluck('name')
            ->intersect(self::GROUPS_MAP[$mainGroup->prev()->id()])
            ->count() > 0
        ;

        $nextGroupExist = $groups
            ->pluck('name')
            ->intersect(self::GROUPS_MAP[$mainGroup->next()->id()])
            ->count() > 0
        ;

        $equalDistances = Collection::make();
        $mainDistance = $this->distanceService->findDistance(self::GROUPS_MAP[$mainGroup->id()], $cupEvent->event_id);
        if ($mainDistance) {
            $equalDistances = $this->distanceService->getEqualDistances($mainDistance);
        }

        $equalGroupsIds = $equalDistances->pluck('group_id');
        foreach ($cupEventProtocolLines as $groupId => $groupProtocolLines) {
            if (
                // это объединение групп
                // тут надо разделять
                ($mainGroupExist && !$nextGroupExist)
                || (!$mainGroupExist && $previousGroupExist)
            ) {
                $eventGroupResults = $this->calculateLines($cupEvent, $groupProtocolLines);
            } else if ($equalGroupsIds->contains($groupId)) {
                $eventGroupResults = $this->calculateLines($cupEvent, $groupProtocolLines);
                dump($eventGroupResults);
            } else {
                $eventGroupResults = $this->calculateGroup($cupEvent, $groupId);
            }

            $results = $results->merge($eventGroupResults->intersectByKeys($groupProtocolLines->keyBy('person_id')));
        }

        return $results->sortByDesc(static fn (CupEventPoint $cupEventResult) => $cupEventResult->points);
    }

    public function getGroups(): Collection|array
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
        $startYear = $cupEvent->cup->year->value - ($group->age()?->value ?: 0);
        $finishYear = $group->age() === $group->age()?->next() ? null : $startYear - 4;

        return $this->protocolLinesRepository->getCupEventProtocolLinesForPersonsCertainAge(
            cupEvent: $cupEvent,
            startYear: $finishYear,
            finishYear: $startYear,
            withPayments: true
        );
    }

    protected function getEventGroups(GroupMale $male): Collection
    {
        $groups = Collection::make();

        /** @var CupGroup $cupGroup */
        foreach ($this->getCalculatedGroups() as $cupGroup) {
            if ($cupGroup->male() === $male) {
                $groups = $groups->merge($this->groupsService->getGroups(static::GROUPS_MAP[$cupGroup->id()]));
            }
        }

        return $groups;
    }
}
