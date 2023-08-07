<?php

namespace App\Models\Cups;

use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Group\CupGroup;
use App\Models\Group\GroupMale;
use Illuminate\Support\Collection;

class BikeCupType extends EliteCupType
{
    public const ELITE_MEN_GROUPS = ['М21_МТВО', 'М21 МТВО', 'М21Е', 'М21E', 'МЕ', 'Мужчины группа Е', 'М21', 'M21E', 'МE', 'М21 Фин Е', 'M21',];
    public const ELITE_WOMEN_GROUPS = ['Ж21_МТВО', 'Ж21 МТВО', 'Ж21', 'Ж21Е', 'W21', 'ЖЕ', 'ЖE', 'Ж21E', 'W21E', 'Ж21 Фин Е', 'Женщины группа Е',];

    public function getId(): string
    {
        return CupType::BIKE;
    }

    public function getNameKey(): string
    {
        return 'app.cup.type.bike';
    }

    /**
     * @param CupEvent $cupEvent
     * @param CupGroup $mainGroup
     * @return Collection //array<int, CupEventPoint>
     */
    public function calculateEvent(CupEvent $cupEvent, CupGroup $mainGroup): Collection
    {
        $cupEventProtocolLines = $this->getGroupProtocolLines($cupEvent, $mainGroup);
        $results = $this->calculateLines($cupEvent, $cupEventProtocolLines);

        return $results->sortByDesc(fn (CupEventPoint $cupEventResult) => $cupEventResult->points);
    }

    protected function getGroupsMap(CupGroup $group): array
    {
        $map = [
            (new CupGroup(GroupMale::Man))->id() => static::ELITE_MEN_GROUPS,
            (new CupGroup(GroupMale::Woman))->id() => static::ELITE_WOMEN_GROUPS,
        ];

        return $map[$group->id()] ?? [];
    }
}
