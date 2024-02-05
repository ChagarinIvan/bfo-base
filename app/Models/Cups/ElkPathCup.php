<?php

declare(strict_types=1);

namespace App\Models\Cups;

use App\Models\CupEvent;
use App\Models\Group\CupGroup;
use App\Models\Group\GroupAge;
use App\Models\Group\GroupMale;
use Illuminate\Support\Collection;

class ElkPathCup extends EliteCupType
{
    protected const GROUPS_MAP = [
        'M_0_Elite–M' => ['Elite–Mужчыны', 'EliteTrail-М'],
        'W_0_Elite–W' => ['Elite–Жанчыны', 'EliteTrail-Ж'],
        'M_0_Short–M' => ['ShortTrail-М,15-34', 'Short–Mужчыны'],
        'W_0_Short–W' => ['ShortTrail-Ж,15-34', 'Short–Жанчыны'],
        'M_35_Short–M-35' => ['ShortTrail-М,35+'],
        'W_35_Short–W-35' => ['ShortTrail-Ж,35+'],
        'M_0_Kids–M' => ['Kids–Хлопцы', 'KidsTrail-М'],
        'W_0_Kids–W' => ['Kids–Дзяўчыны', 'KidsTrail-Ж'],
        'M_0_Youth–M' => ['Youth–Хлопцы', 'YouthTrail-М'],
        'W_0_Youth–W' => ['Youth–Дзяўчыны', 'YouthTrail-Ж'],
    ];

    protected static function withPayments(): bool
    {
        return false;
    }
    public function getId(): string
    {
        return CupType::ELK_PATH;
    }

    public function getNameKey(): string
    {
        return 'app.cup.type.elk_path';
    }

    /**
     * @return Collection&CupGroup[]
     */
    public function getGroups(): Collection
    {
        $groups = Collection::make();

        $groups->push(new CupGroup(GroupMale::Man, name: 'Elite–M'));
        $groups->push(new CupGroup(GroupMale::Woman, name: 'Elite–W'));
        $groups->push(new CupGroup(GroupMale::Man, name: 'Short–M'));
        $groups->push(new CupGroup(GroupMale::Woman, name: 'Short–W'));
        $groups->push(new CupGroup(GroupMale::Man, age: GroupAge::a35, name: 'Short–M-35'));
        $groups->push(new CupGroup(GroupMale::Man, age: GroupAge::a35, name: 'Short–W-35'));
        $groups->push(new CupGroup(GroupMale::Man, name: 'Kids–W'));
        $groups->push(new CupGroup(GroupMale::Woman, name: 'Kids–M'));
        $groups->push(new CupGroup(GroupMale::Man, name: 'Youth–M'));
        $groups->push(new CupGroup(GroupMale::Woman, name: 'Youth–W'));

        return $groups;
    }

    protected function getGroupProtocolLines(CupEvent $cupEvent, CupGroup $group): Collection
    {
        $groupMap = $this->getGroupsMap($group);
        $mainDistance = $this->distanceService->findDistance($groupMap, $cupEvent->event_id);

        if ($mainDistance === null) {
            return new Collection();
        }

        return $this->protocolLinesRepository->getCupEventDistancesProtocolLines(collect([$mainDistance]), $cupEvent, static::withPayments());
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
