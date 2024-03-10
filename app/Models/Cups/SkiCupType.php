<?php

declare(strict_types=1);

namespace App\Models\Cups;

use App\Models\Cup;
use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Group\CupGroup;
use App\Models\Group\GroupMale;
use App\Models\Year;
use Illuminate\Support\Collection;

class SkiCupType extends EliteCupType
{
    public const ELITE_MEN_GROUPS = ['М21', 'М21Е', 'М21E', 'МЕ', 'Мужчины группа Е', 'М21', 'M21E', 'МE', 'М21 Фин Е', 'M21'];
    public const ELITE_WOMEN_GROUPS = ['Ж21', 'Ж21Е', 'W21', 'ЖЕ', 'ЖE', 'Ж21E', 'W21E', 'Ж21 Фин Е', 'Женщины группа Е',];

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

        return $this
            ->calculateLines($cupEvent, $cupEventProtocolLines)
            ->sortByDesc(static fn (CupEventPoint $cupEventResult) => $cupEventResult->points)
        ;
    }

    protected function paymentYear(Cup $cup): Year
    {
        return Year::from($cup->year)->previous();
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
