<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupType;

use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupEvent\CupEventPoint;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Cup\Group\CupGroupFactory;
use App\Domain\Cup\Group\GroupAge;
use App\Domain\ProtocolLine\Criteria\CupEventDistancesProtocolLinesCriteria;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Models\Year;
use Illuminate\Support\Collection;

/**
 * При начислении очков Кубка БФО среди ветеранов спортсмен получает очки в зачет только своей возрастной группы.
 * В случае, если спортсмен на этапе участвует в возрастной группе, которая младше, чем группа,
 * соответствующая его возрасту, то очки начисляются в зачет группы по возрасту спортсмена,
 * но по расчету для той группы, в которой он участвовал. Если при этом дистанции групп совпадают,
 * то результат спортсмена переносится в его группу по возрасту.
 *
 * В случае если организаторы этапа Кубка БФО среди ветеранов объединяют соседние возрастные группы (в том числе и с группой МЖ21Е),
 * протокол результатов объединенной группы разделяется в соответствии с возрастными группами спортсменов и очки для Кубка БФО
 * среди ветеранов рассчитываются отдельно для каждой возрастной группы.
 * В данном случае, результат спортсмена из более старшей возрастной группы, чем получается при делении,
 * участвует в расчете в наиболее старшей группе, которая в итоге получилась при делении.
 */
class NewMasterCupType extends AbstractCupType
{
    protected const GROUPS_MAP = [
        'M_21_' => ['МE(35)'],
        'M_35_' => ['M35', 'М35'],
        'M_40_' => ['M40', 'М40'],
        'M_45_' => ['M45', 'М45'],
        'M_50_' => ['M50', 'М50'],
        'M_55_' => ['M55', 'М55'],
        'M_60_' => ['M60', 'М60'],
        'M_65_' => ['M65', 'М65'],
        'M_70_' => ['M70', 'М70'],
        'M_75_' => ['M75', 'М75'],
        'M_80_' => ['M80', 'М80'],
        'W_21_' => ['ЖE(35)'],
        'W_35_' => ['Ж35', 'W35'],
        'W_40_' => ['Ж40', 'W40'],
        'W_45_' => ['Ж45', 'W45'],
        'W_50_' => ['Ж50', 'W50'],
        'W_55_' => ['Ж55', 'W55'],
        'W_60_' => ['Ж60', 'W60'],
        'W_65_' => ['Ж65', 'W65'],
        'W_70_' => ['Ж70', 'W70'],
        'W_75_' => ['Ж75', 'W75'],
        'W_80_' => ['Ж80', 'W80'],
    ];

    public function getNameKey(): string
    {
        return 'app.cup.type.master.new';
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
        return CupGroupFactory::getAgeTypeGroups([
            GroupAge::a35,
            GroupAge::a40,
            GroupAge::a45,
            GroupAge::a50,
            GroupAge::a55,
            GroupAge::a60,
            GroupAge::a65,
            GroupAge::a70,
            GroupAge::a75,
            GroupAge::a80,
        ]);
    }

    protected function getGroupProtocolLines(CupEvent $cupEvent, CupGroup $group): Collection
    {
        // пытаемся посчитать для группы которая есть на соревнования М55
        // берём всех спортсменов из группы (они есть).
        // группируем их по возрастным группам типа М55 => [], M60 => [], М65 => []
        // смотрим были ли на соревнования эти группы, от большей,
        // например если М65 не было или были эквиваленты дистанции, то может туда отправить тех кто сгруппирован под этой группой,
        // если такая группа есть, то идём к следующией и в более нижнюю добавляем спортсменов сверху.
        // если находится группа которой не было на соревнованиях, то спортсмены попадают туда и не считаются для нашей группы
        // если не находится "свободной", то все считаются по запрашиваемой группе М55

        $lines = $this->getProtocolLines($cupEvent, $group);

        if (!$lines->isEmpty()) {
            return $this->getAgeProtocolLines($cupEvent, $group, $group);
        }

        $result = collect();
        $prevGroup = $group;

        while (true) {
            $prevGroup = $prevGroup->prev();
            if (!array_key_exists($prevGroup->id(), self::GROUPS_MAP) && $prevGroup->id() !== 'M_21_' && $prevGroup->id() !== 'W_21_') {
                return $result;
            }

            $result = $result->merge($this->getAgeProtocolLines($cupEvent, $prevGroup, $group));
        }
    }

    private function getAgeProtocolLines(CupEvent $cupEvent, CupGroup $mainGroup, CupGroup $searchGroup): Collection
    {
        $mainDistance = $this->distanceService->findDistance(self::GROUPS_MAP[$searchGroup->id()], $cupEvent->event_id);
        $lines = $this->getProtocolLines($cupEvent, $mainGroup);

        if (!$lines->isEmpty()) {
            $groupedByGroupNameLines = $lines
                ->filter(static fn(ProtocolLine $line): bool =>
                    35 <= ($cupEvent->cup->year->value - $line->person?->birthday?->year)
                    && ($cupEvent->cup->year->value - $line->person?->birthday?->year) <= 100)
                ->groupBy(static fn(ProtocolLine $line): string => (new CupGroup(
                    $mainGroup->male(),
                    self::calculateGroupAge($cupEvent->cup->year->value - $line->person?->birthday?->year),
                ))->id()
                )
                ->sortKeys(descending: true)
            ;

            dump($groupedByGroupNameLines);
            $firstKey = $groupedByGroupNameLines->keys()->first();
            if (!$firstKey) {
                return collect();
            }

            $firstGroup = $this->groupFactory->fromId((string)$firstKey);
            if ($firstGroup->age()->value < $searchGroup->age()->value) {
                return collect();
            }

            while ($groupedByGroupNameLines->isNotEmpty()) {
                /**
                 * @var ProtocolLine[] $groupLines
                 * @var string $ageGroup
                 */
                foreach ($groupedByGroupNameLines as $ageGroup => $groupLines) {
                    $aGroup = $this->groupFactory->fromId($ageGroup);

                    if ($aGroup->equal($searchGroup)) {
                        return $groupLines;
                    }

                    $ageGroupLines = $this->getProtocolLines($cupEvent, $aGroup);

                    if ($ageGroupLines->isEmpty()) {
                        // то группа свободна и можно этих не считать, они уйдут в неё
                        continue;
                    }

                    $aDistance = $this->distanceService->findDistance(self::GROUPS_MAP[$aGroup->id()], $cupEvent->event_id);

                    if ($aDistance && $mainDistance && $mainDistance->equal($aDistance)) {
                        continue;
                    }

                    $key = $aGroup->prev()->id();

                    $mergedLines = ($groupedByGroupNameLines->get($key) ?? collect())->merge($groupLines);

                    // добавіть ремув логік
                    // Обновим коллекцию
                    $groupedByGroupNameLines = $groupedByGroupNameLines->forget($key);
                    if ($groupedByGroupNameLines->isEmpty()) {
                        return collect();
                    }

                    $groupedByGroupNameLines = $groupedByGroupNameLines
                        ->put($key, $mergedLines)
                        ->forget($aGroup->id())
                        ->sortKeys(descending: true)
                    ;

                    // После изменения — break, чтобы начать обход заново
                    break;
                }
            }
        }

        return Collection::empty();
    }

    private static function calculateGroupAge(int $age): GroupAge
    {
        $map = [
            35 => GroupAge::a35,
            36 => GroupAge::a35,
            37 => GroupAge::a35,
            38 => GroupAge::a35,
            39 => GroupAge::a35,
            40 => GroupAge::a40,
            41 => GroupAge::a40,
            42 => GroupAge::a40,
            43 => GroupAge::a40,
            44 => GroupAge::a40,
            45 => GroupAge::a45,
            46 => GroupAge::a45,
            47 => GroupAge::a45,
            48 => GroupAge::a45,
            49 => GroupAge::a45,
            50 => GroupAge::a50,
            51 => GroupAge::a50,
            52 => GroupAge::a50,
            53 => GroupAge::a50,
            54 => GroupAge::a50,
            55 => GroupAge::a55,
            56 => GroupAge::a55,
            57 => GroupAge::a55,
            58 => GroupAge::a55,
            59 => GroupAge::a55,
            60 => GroupAge::a60,
            61 => GroupAge::a60,
            62 => GroupAge::a60,
            63 => GroupAge::a60,
            64 => GroupAge::a60,
            65 => GroupAge::a65,
            66 => GroupAge::a65,
            67 => GroupAge::a65,
            68 => GroupAge::a65,
            69 => GroupAge::a65,
            70 => GroupAge::a70,
            71 => GroupAge::a70,
            72 => GroupAge::a70,
            73 => GroupAge::a70,
            74 => GroupAge::a70,
            75 => GroupAge::a75,
            76 => GroupAge::a75,
            77 => GroupAge::a75,
            78 => GroupAge::a75,
            79 => GroupAge::a75,
            80 => GroupAge::a80,
            81 => GroupAge::a80,
            82 => GroupAge::a80,
            83 => GroupAge::a80,
            84 => GroupAge::a80,
            85 => GroupAge::a80,
            86 => GroupAge::a80,
            87 => GroupAge::a80,
            88 => GroupAge::a80,
            89 => GroupAge::a80,
            90 => GroupAge::a80,
            91 => GroupAge::a80,
            92 => GroupAge::a80,
            93 => GroupAge::a80,
            94 => GroupAge::a80,
            95 => GroupAge::a80,
        ];

        return $map[$age] ?? GroupAge::a80;
    }
    protected function getProtocolLines(CupEvent $cupEvent, CupGroup $group): Collection
    {
        $mainDistance = $this->distanceService->findDistance(self::GROUPS_MAP[$group->id()], $cupEvent->event_id);
        $equalDistances = Collection::make([$mainDistance]);

        if ($mainDistance) {
            $equalDistances->push(...$this->distanceService->getEqualDistances($mainDistance));
        }

        return $this->protocolLinesRepository->byCriteria(
            CupEventDistancesProtocolLinesCriteria::create($equalDistances, $cupEvent, $this->paymentYear($cupEvent->cup))
        );
    }

    protected function paymentYear(Cup $cup): Year
    {
        return $cup->year;
    }
}
