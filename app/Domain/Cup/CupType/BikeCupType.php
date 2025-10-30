<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupType;

use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupEvent\CupEventPoint;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Cup\Group\GroupMale;
use Illuminate\Support\Collection;
use function array_merge;

class BikeCupType extends EliteCupType
{
    public const ELITE_MEN_GROUPS = ['Мужчины', 'М21_МТВО', 'М21МТВО', 'М21 МТВО', 'М21Е', 'М21E', 'МЕ', 'Мужчины группа Е', 'М21', 'M21E', 'МE', 'М21 Фин Е', 'M21',];
    public const ELITE_WOMEN_GROUPS = ['Женщины', 'Ж21_МТВО', 'Ж21МТВО', 'Ж21 МТВО', 'Ж21', 'Ж21Е', 'W21', 'ЖЕ', 'ЖE', 'Ж21E', 'W21E', 'Ж21 Фин Е', 'Женщины группа Е',];

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

        return $this
            ->calculateLines($cupEvent, $cupEventProtocolLines)
            ->sortByDesc(static fn (CupEventPoint $cupEventResult) => $cupEventResult->points)
        ;
    }

    protected function getGroupsMap(CupGroup $group): array
    {
        $map = [
            (new CupGroup(GroupMale::Man))->id() => static::ELITE_MEN_GROUPS,
            (new CupGroup(GroupMale::Woman))->id() => static::ELITE_WOMEN_GROUPS,
        ];

        return $map[$group->id()] ?? [];
    }

    protected function getAllGroupsMap(CupGroup $group): array
    {
        $map = [
            (new CupGroup(GroupMale::Man))->id() => array_merge(
                self::ELITE_MEN_GROUPS,
                ['M20', 'М20', 'M21A', 'M21А', 'М21А', 'М21A']
            ),
            (new CupGroup(GroupMale::Woman))->id() => array_merge(
                self::ELITE_WOMEN_GROUPS,
                ['Ж20', 'W20', 'Ж21A', 'W20А', 'Ж21А', 'W20A']
            )
        ];

        return $map[$group->id()] ?? [];
    }
}
