<?php

namespace App\Models\Cups;

use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Group\CupGroup;
use Illuminate\Support\Collection;

class SkiCupType extends EliteCupType
{
    public const MEN_GROUPS = ['М21', 'М21Е', 'М21E', 'МЕ', 'Мужчины группа Е', 'М21', 'M21E', 'МE', 'М21 Фин Е', 'M21'];
    public const WOMEN_GROUPS = ['Ж21', 'Ж21Е', 'W21', 'ЖЕ', 'ЖE', 'Ж21E', 'W21E', 'Ж21 Фин Е', 'Женщины группа Е',];

    public function getId(): string
    {
        return CupType::SKI;
    }

    public function getNameKey(): string
    {
        return 'app.cup.type.ski';
    }

    /**
     * @param CupEvent $cupEvent
     * @param CupGroup $mainGroup
     * @return Collection // array<int, CupEventPoint>
     */
    public function calculateEvent(CupEvent $cupEvent, CupGroup $mainGroup): Collection
    {
        $cupEventProtocolLines = $this->getGroupProtocolLines($cupEvent, $mainGroup);
        $results = $this->calculateLines($cupEvent, $cupEventProtocolLines);

        return $results->sortByDesc(fn (CupEventPoint $cupEventResult) => $cupEventResult->points);
    }
}