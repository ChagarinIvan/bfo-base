<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupType;

use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Cup\Group\GroupAge;
use App\Domain\Cup\Group\GroupMale;
use App\Domain\ProtocolLine\Criteria\CupEventDistancesProtocolLinesCriteria;
use Illuminate\Support\Collection;

class ElkPathCup extends EliteCupType
{
    protected const GROUPS_MAP = [
        'M_0_Elite–M' => ['Elite–Mужчыны', 'EliteTrail-М', '%Elite-М'],
        'W_0_Elite–W' => ['Elite–Жанчыны', 'EliteTrail-Ж', '%Elite-Ж'],
        'M_0_Short–M' => ['ShortTrail-М,15-34', 'Short–Mужчыны', '%Short-М%34'],
        'W_0_Short–W' => ['ShortTrail-Ж,15-34', 'Short–Жанчыны', '%Short-Ж%34'],
        'M_35_Short–M-35' => ['ShortTrail-М,35+', 'Short–M-35', '%Short-М%35%'],
        'W_35_Short–W-35' => ['ShortTrail-Ж,35+', 'Short–M-35', '%Short-Ж%35%'],
        'M_0_Kids–M' => ['Kids–Хлопцы', 'KidsTrail-М', '%Kids-М'],
        'W_0_Kids–W' => ['Kids–Дзяўчыны', 'KidsTrail-Ж', '%Kids-Ж'],
        'M_0_Youth–M' => ['Youth–Хлопцы', 'YouthTrail-М', '%Youth-М'],
        'W_0_Youth–W' => ['Youth–Дзяўчыны', 'YouthTrail-Ж', '%Youth-Ж'],
        'W_0_OpenTrail-W' => ['OpenTrail-Ж', 'Open-Дзяўчыны', '%Open-Ж'],
        'M_0_OpenTrail-M' =>  ['OpenTrail-М', 'Open-Хлопцы', '%Open-М'],
    ];

    public function getNameKey(): string
    {
        return 'app.cup.type.elk_path';
    }

    public function getGroups(): Collection|array
    {
        $groups = Collection::make();

        $groups->push(new CupGroup(GroupMale::Man, name: 'Elite–M'));
        $groups->push(new CupGroup(GroupMale::Woman, name: 'Elite–W'));
        $groups->push(new CupGroup(GroupMale::Man, name: 'Short–M'));
        $groups->push(new CupGroup(GroupMale::Woman, name: 'Short–W'));
        $groups->push(new CupGroup(GroupMale::Man, age: GroupAge::a35, name: 'Short–M-35'));
        $groups->push(new CupGroup(GroupMale::Woman, age: GroupAge::a35, name: 'Short–W-35'));
        $groups->push(new CupGroup(GroupMale::Man, name: 'Kids–M'));
        $groups->push(new CupGroup(GroupMale::Woman, name: 'Kids–W'));
        $groups->push(new CupGroup(GroupMale::Man, name: 'Youth–M'));
        $groups->push(new CupGroup(GroupMale::Woman, name: 'Youth–W'));
        $groups->push(new CupGroup(GroupMale::Woman, name: 'OpenTrail-W'));
        $groups->push(new CupGroup(GroupMale::Man, name: 'OpenTrail-M'));

        return $groups;
    }

    protected function getGroupProtocolLines(CupEvent $cupEvent, CupGroup $group): Collection
    {
        $groupMap = $this->getGroupsMap($group);
        $mainDistance = $this->distanceService->findDistance($groupMap, $cupEvent->event_id);

        if ($mainDistance === null) {
            return new Collection();
        }

        return $this->protocolLinesRepository->byCriteria(
            CupEventDistancesProtocolLinesCriteria::create(collect([$mainDistance]), $cupEvent)
        );
    }

    protected function getGroupsMap(CupGroup $group): array
    {
        foreach ($this->getGroups() as $cupGroup) {
            if ($cupGroup->equal($group)) {
                return self::GROUPS_MAP[$cupGroup->id()];
            }
        }

        return [];
    }
}
