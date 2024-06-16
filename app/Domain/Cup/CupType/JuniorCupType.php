<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupType;

use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Cup\Group\CupGroupFactory;
use App\Domain\Cup\Group\GroupAge;
use App\Domain\Cup\Group\GroupMale;
use App\Domain\Distance\Distance;
use App\Domain\ProtocolLine\ProtocolLine;
use Illuminate\Support\Collection;
use function array_merge;
use function in_array;
use function round;

/**
 * M20 i Ж20
 * Юніёрскі
 *
 * бяру усе группы якія аднолькавыя да М20
 * бяру з іх толькі членаў бфо каму 20 год і вылічваю самага хуткага і яму максімум балаў
 */
class JuniorCupType extends EliteCupType
{
    public const MEN_MAIN_GROUPS_NAMES = ['М20', 'M20'];

    public const WOMEN_MAIN_GROUPS_NAMES = ['Ж20', 'W20'];

    private static array $map = [];

    public function getNameKey(): string
    {
        return 'app.cup.type.junior';
    }

    public function getGroups(): Collection|array
    {
        return CupGroupFactory::getAgeTypeGroups([GroupAge::a20]);
    }

    protected function getGroupProtocolLines(CupEvent $cupEvent, CupGroup $group): Collection
    {
        $startYear = $cupEvent->cup->year->value - ($group->age()?->value ?: 0);
        $finishYear = $startYear + 4;

        $juniorProtocolLines = $this->protocolLinesRepository->getCupEventProtocolLinesForPersonsCertainAge(
            cupEvent: $cupEvent,
            startYear: $startYear,
            finishYear: $finishYear,
            withPayments: true
        );

        $mainGroupsNames = $group->male() === GroupMale::Man ? self::MEN_MAIN_GROUPS_NAMES : self::WOMEN_MAIN_GROUPS_NAMES;
        $mainGroups = $this->groupsService->getGroups($mainGroupsNames);
        $eliteGroupsNames = $group->male() === GroupMale::Man ? EliteCupType::ELITE_MEN_GROUPS : EliteCupType::ELITE_WOMEN_GROUPS;
        $eliteGroupsList = $this->groupsService->getGroups($eliteGroupsNames);

        if ($mainGroups->isEmpty()) {
            $mainGroups = $eliteGroupsList;
        }

        $groupNames = $mainGroups->pluck('name')->all();
        $mainDistance = $this->distanceService->findDistance($groupNames, $cupEvent->event_id);
        if ($mainDistance === null) {
            return new Collection();
        }

        $distances = $this->distanceService
            ->getEqualDistances($mainDistance)
            ->add($mainDistance)
            ->filter(fn (Distance $distance) => in_array($distance->group->name, $this->getAllGroupsMap($group), true))
            ->pluck('id')
            ->all()
        ;

        return $juniorProtocolLines->filter(
            static fn (ProtocolLine $protocolLine) => in_array($protocolLine->distance_id, $distances, true)
        );
    }

    protected function getAllGroupsMap(CupGroup $group): array
    {
        if (empty(self::$map)) {
            self::$map = [
                GroupMale::Man->name => array_merge(
                    EliteCupType::ELITE_MEN_GROUPS,
                    self::MEN_MAIN_GROUPS_NAMES,
                    ['M18', 'М18', 'M16', 'М16', 'M21A', 'M21А', 'М21A', 'М21А',]
                ),
                GroupMale::Woman->name => array_merge(
                    EliteCupType::ELITE_WOMEN_GROUPS,
                    self::WOMEN_MAIN_GROUPS_NAMES,
                    ['Ж18', 'W18', 'Ж16', 'W16', 'W21A', 'W21А', 'Ж21A', 'Ж21А',]
                )
            ];
        }

        return self::$map[$group->male()->name] ?? [];
    }
}
