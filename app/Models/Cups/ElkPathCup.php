<?php

namespace App\Models\Cups;

use App\Models\CupEvent;
use App\Models\Group\CupGroup;
use App\Models\Group\GroupMale;
use Illuminate\Support\Collection;

class ElkPathCup extends EliteCupType
{
    public function getId(): string
    {
        return CupType::ELK_PATH;
    }

    public function getNameKey(): string
    {
        return 'app.cup.type.elk_path';
    }

    public function getGroups(): Collection
    {
        $groups = Collection::make();

        $groups->push(new CupGroup(GroupMale::Man, name: 'Elite–Mужчыны'));
        $groups->push(new CupGroup(GroupMale::Woman, name: 'Elite–Жанчыны'));
        $groups->push(new CupGroup(GroupMale::Man, name: 'Short–Mужчыны'));
        $groups->push(new CupGroup(GroupMale::Woman, name: 'Short–Жанчыны'));
        $groups->push(new CupGroup(GroupMale::Man, name: 'Kids–Хлопцы'));
        $groups->push(new CupGroup(GroupMale::Woman, name: 'Kids–Дзяўчыны'));
        $groups->push(new CupGroup(GroupMale::Man, name: 'Youth–Хлопцы'));
        $groups->push(new CupGroup(GroupMale::Woman, name: 'Youth–Дзяўчыны'));

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
        $map = [];
        foreach ($this->getGroups() as $cupGroup) {
            if ($cupGroup->equal($group)) {
                return [$cupGroup->name()];
            }
        }

        return [];
    }

    protected static function withPayments(): bool
    {
        return false;
    }
}
