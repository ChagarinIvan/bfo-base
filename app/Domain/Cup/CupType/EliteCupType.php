<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupType;

use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupEvent\CupEventPoint;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Cup\Group\CupGroupFactory;
use App\Domain\Cup\Group\GroupMale;
use App\Domain\Distance\Distance;
use App\Domain\ProtocolLine\Criteria\CupEventDistancesProtocolLinesCriteria;
use App\Models\Year;
use Illuminate\Support\Collection;
use function array_merge;
use function in_array;

class EliteCupType extends AbstractCupType
{
    public const ELITE_MEN_GROUPS = ['М21Е', 'М21E', 'МЕ', 'MЕ', 'Мужчины группа Е', 'М21', 'M21E', 'МE', 'М21 Фин Е', 'M21', 'МE(35)', 'М35', 'M35', 'М40', 'M40'];
    public const ELITE_WOMEN_GROUPS = ['Ж21', 'Ж21Е', 'W21', 'ЖЕ', 'ЖE', 'Ж21E', 'W21E', 'Ж21 Фин Е', 'Женщины группа Е', 'ЖE(35)', 'Ж35', 'Ж40', 'W35', 'W40'];

    private static array $map = [];

    public function getNameKey(): string
    {
        return 'app.cup.type.elite';
    }

    public function calculateEvent(CupEvent $cupEvent, CupGroup $mainGroup): Collection
    {
        $cupEventProtocolLines = $this->getGroupProtocolLines($cupEvent, $mainGroup);

        return $this
            ->calculateLines($cupEvent, $cupEventProtocolLines)
            ->sortByDesc(static fn (CupEventPoint $cupEventResult) => $cupEventResult->points)
        ;
    }

    public function getGroups(): Collection|array
    {
        return CupGroupFactory::getAgeTypeGroups();
    }

    protected function paymentYear(Cup $cup): Year
    {
        return $cup->year;
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

        return $this->protocolLinesRepository->byCriteria(
            CupEventDistancesProtocolLinesCriteria::create($distances, $cupEvent, $this->paymentYear($cupEvent->cup))
        );
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
        if (empty(self::$map)) {
            self::$map = [
                (new CupGroup(GroupMale::Man))->id() => array_merge(
                    self::ELITE_MEN_GROUPS,
                    JuniorCupType::MEN_MAIN_GROUPS_NAMES,
                    ['M18', 'М18', 'M21A', 'M21А', 'М21A', 'М21А',]
                ),
                (new CupGroup(GroupMale::Woman))->id() => array_merge(
                    self::ELITE_WOMEN_GROUPS,
                    JuniorCupType::WOMEN_MAIN_GROUPS_NAMES,
                    ['Ж18', 'W18', 'W21A', 'W21А', 'Ж21A', 'Ж21А',]
                )
            ];
        }

        return self::$map[$group->id()] ?? [];
    }
}
