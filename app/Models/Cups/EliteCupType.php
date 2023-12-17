<?php

namespace App\Models\Cups;

use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Distance;
use App\Models\Group\CupGroup;
use App\Models\Group\CupGroupFactory;
use App\Models\Group\GroupMale;
use Illuminate\Support\Collection;

class EliteCupType extends AbstractCupType
{
    public const ELITE_MEN_GROUPS = ['М21Е', 'М21E', 'МЕ', 'Мужчины группа Е', 'М21', 'M21E', 'МE', 'М21 Фин Е', 'M21', 'МE(35)'];
    public const ELITE_WOMEN_GROUPS = ['Ж21', 'Ж21Е', 'W21', 'ЖЕ', 'ЖE', 'Ж21E', 'W21E', 'Ж21 Фин Е', 'Женщины группа Е', 'ЖE(35)'];

    public function getId(): string
    {
        return CupType::ELITE;
    }

    public function getNameKey(): string
    {
        return 'app.cup.type.elite';
    }

    public function calculateEvent(CupEvent $cupEvent, CupGroup $mainGroup): Collection
    {
        $cupEventProtocolLines = $this->getGroupProtocolLines($cupEvent, $mainGroup);

        return $this
            ->calculateLines($cupEvent, $cupEventProtocolLines)
            ->sortByDesc(fn (CupEventPoint $cupEventResult) => $cupEventResult->points)
        ;
    }

    protected function getGroupProtocolLines(CupEvent $cupEvent, CupGroup $group): Collection
    {
        $groupMap = $this->getGroupsMap($group);
        $mainDistance = $this->distanceService->findDistance($groupMap, $cupEvent->event_id);
        if ($mainDistance === null) {
            return new Collection();
        }
        $equalDistances = $this->distanceService->getEqualDistances($mainDistance);

        $distances = $equalDistances
            ->add($mainDistance)
            ->filter(fn (Distance $distance) => in_array($distance->group->name, $this->getAllGroupsMap($group), true))
        ;

        return $this->protocolLinesRepository->getCupEventDistancesProtocolLines($distances, $cupEvent, static::withPayments());
    }

    protected static function withPayments(): bool
    {
        return true;
    }

    public function getGroups(): Collection
    {
        return CupGroupFactory::getAgeTypeGroups();
    }

    protected function getGroupsMap(CupGroup $group): array
    {
        $map = [
            (new CupGroup(GroupMale::Man))->id() => self::ELITE_MEN_GROUPS,
            (new CupGroup(GroupMale::Woman))->id() => self::ELITE_WOMEN_GROUPS,
        ];

        return $map[$group->id()] ?? [];
    }

    protected function getAllGroupsMap(CupGroup $group): array
    {
        $map = [
            (new CupGroup(GroupMale::Man))->id() => array_merge(
                self::ELITE_MEN_GROUPS,
                JuniorCupType::MEN_MAIN_GROUPS_NAMES,
                ['M18', 'М18']
            ),
            (new CupGroup(GroupMale::Woman))->id() => array_merge(
                self::ELITE_WOMEN_GROUPS,
                JuniorCupType::WOMEN_MAIN_GROUPS_NAMES,
                ['Ж18', 'W18']
            )
        ];

        return $map[$group->id()] ?? [];
    }
}
