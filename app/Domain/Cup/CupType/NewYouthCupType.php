<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupType;

use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupEvent\CupEventPoint;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Cup\Group\CupGroupFactory;
use App\Domain\Cup\Group\GroupAge;
use App\Domain\Cup\Group\GroupMale;
use App\Domain\Group\Group;
use App\Domain\ProtocolLine\ProtocolLine;
use Illuminate\Support\Collection;
use function array_map;
use function in_array;

/**
 * ЮНАЦКІ новы
 *
 */
class NewYouthCupType extends MasterCupType
{
    protected const GROUPS_MAP = [
        'M_12_' => ['M12', 'М12'],
        'M_14_' => ['M14', 'М14'],
        'M_16_' => ['M16', 'М16'],
        'M_18_' => ['M18', 'М18'],
        'M_20_' => ['M20', 'М20'],
        'M_21_' => [
            'М21Е',
            'М21E',
            'МЕ',
            'Мужчины группа Е',
            'М21',
            'M21E',
            'МE(35)',
            'МE',
            'М21 Фин Е',
            'M21',
        ],
        'W_12_' => ['Ж12', 'W12'],
        'W_14_' => ['Ж14', 'W14'],
        'W_16_' => ['Ж16', 'W16'],
        'W_18_' => ['Ж18', 'W18'],
        'W_20_' => ['Ж20', 'W20'],
        'W_21_' => [
            'Ж21',
            'Ж21Е',
            'W21',
            'ЖЕ',
            'ЖE',
            'ЖE(35)',
            'Ж21E',
            'W21E',
            'Ж21 Фин Е',
            'Женщины группа Е',
        ],
    ];

    public function getNameKey(): string
    {
        return 'app.cup.type.new_youth';
    }

    public function calculateEvent(CupEvent $cupEvent, CupGroup $mainGroup): Collection
    {
        $results = new Collection();
        $cupEventProtocolLines = $this->getGroupProtocolLines($cupEvent, $mainGroup);
        $eventGroupsId = $this->getCupEventGroups($mainGroup)->pluck('id');

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
        $cupEventProtocolLines = $cupEventProtocolLines->flatten(1)->groupBy('distance_id');

        $haystack = self::GROUPS_MAP[$mainGroup->id()];
        /** @var Group|null $mainGroupId */
        $mainGroupId = $this->getCupEventGroups($mainGroup)
            ->filter(static fn (Group $group) => in_array($group->name, $haystack, true))
            ->first()
        ;
        dump($haystack);
        dump($mainGroupId);
        dd($cupEventProtocolLines->keys());

        $mainGroupExists = $mainGroupId && $cupEventProtocolLines->has($mainGroupId->id);

        foreach ($cupEventProtocolLines as $distanceId => $groupProtocolLines) {
            /** @var Collection $groupProtocolLines */
            $ids = $groupProtocolLines->pluck('person_id');
            $eventGroupResults = $this->calculateDistance($cupEvent, $distanceId, $mainGroupExists ? null : $ids);
            $eventGroupResults = $eventGroupResults->filter(static fn (CupEventPoint $cupEventResult) => $ids->contains($cupEventResult->protocolLine->id));
            $results = $results->merge($eventGroupResults->intersectByKeys($groupProtocolLines->keyBy('person_id')));
        }

        return $results->sortByDesc(static fn (CupEventPoint $cupEventResult) => $cupEventResult->points);
    }

    public function getGroups(): Collection|array
    {
        return CupGroupFactory::getAgeTypeGroups([GroupAge::a12, GroupAge::a14, GroupAge::a16, GroupAge::a18]);
    }

    public function getCalculatedGroups(): Collection
    {
        return CupGroupFactory::getAgeTypeGroups([GroupAge::a12, GroupAge::a14, GroupAge::a16, GroupAge::a18, GroupAge::a20, GroupAge::a21]);
    }

    protected function getCupGroups(CupGroup $group): Collection
    {
        $ages = match ($group->age()) {
            GroupAge::a12 => [GroupAge::a12, GroupAge::a14],
            GroupAge::a14 => [GroupAge::a14, GroupAge::a16],
            GroupAge::a16 => [GroupAge::a16, GroupAge::a18],
            default => [GroupAge::a18, GroupAge::a20, GroupAge::a21],
        };

        return collect(array_map(static fn (GroupAge $age): CupGroup => new CupGroup($group->male(), $age), $ages));
    }

    protected function getCupEventGroups(CupGroup $group): Collection
    {
        $groups = Collection::make();

        /** @var CupGroup $cupGroup */
        foreach ($this->getCupGroups($group) as $cupGroup) {
            $groups = $groups->merge($this->groupsService->getGroups(static::GROUPS_MAP[$cupGroup->id()]));
        }

        return $groups;
    }

    protected function getGroupProtocolLines(CupEvent $cupEvent, CupGroup $group): Collection
    {
        $year = $cupEvent->cup->year->value;
        $startYear = $year - ($group->age()?->value ?: 0);
        $finishYear = $group->age() === GroupAge::a12
            ? $year
            : $startYear + 1
        ;

        return $this->protocolLinesRepository->getCupEventProtocolLinesForPersonsCertainAge(
            cupEvent: $cupEvent,
            startYear: $startYear,
            finishYear: $finishYear,
            citizhenship: true
        );
    }

    private function calculateDistance(CupEvent $cupEvent, int $distanceId, ?Collection $ids): Collection
    {
        $distanceParticipants = $this->protocolLinesRepository->getCupEventDistanceProtocolLines($distanceId);
        if ($ids) {
            $distanceParticipants = $distanceParticipants->filter(static fn (ProtocolLine $line) => $ids->contains($line->person_id));
        }

        return $this->calculateLines(
            $cupEvent,
            $distanceParticipants,
        );
    }
}
