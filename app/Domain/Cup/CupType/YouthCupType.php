<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupType;

use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupEvent\CupEventPoint;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Cup\Group\CupGroupFactory;
use App\Domain\Cup\Group\GroupAge;
use App\Domain\ProtocolLine\ProtocolLine;
use Illuminate\Support\Collection;
use function round;

/**
 * ЮНАЦКІ
 */
class YouthCupType extends MasterCupType
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
            'МE',
            'М21 Фин Е',
            'M21',
            'М21А',
            'М21A',
            'M21A',
            'М21 Фин А',
            'МА',
            'МA',
            'Мужчины группа А',
            'Мужчины группа В',
            'МB',
            'M21B',
            'МВ',
            'М21B',
            'М21Б',
            'М20',
            'M20',
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
            'Ж21E',
            'W21E',
            'Ж21 Фин Е',
            'Женщины группа Е',
            'Ж21А',
            'Ж21A',
            'ЖA',
            'ЖА',
            'W21A',
            'Женщины группа А',
            'Ж21Б',
            'Женщины группа В',
            'Ж21B',
            'W21B',
            'ЖB',
            'ЖВ',
            'Ж20',
            'W20',
        ],
    ];
    private const EVENTS_GROUPS_KOEF = [
        //М21Е
        'М21Е' => 1,
        'М21E' => 1,
        'МЕ' => 1,
        'Мужчины группа Е' => 1,
        'М21' => 1,
        'M21E' => 1,
        'МE' => 1,
        'М21 Фин Е' => 1,
        'M21' => 1,
        //М21А
        'М21А' => 0.9,
        'М21A' => 0.9,
        'M21A' => 0.9,
        'М21 Фин А' => 0.9,
        'МА' => 0.9,
        'МA' => 0.9,
        'Мужчины группа А' => 0.9,
        //М21Б
        'Мужчины группа В' => 0.9,
        'МB' => 0.85,
        'M21B' => 0.85,
        'МВ' => 0.85,
        'М21B' => 0.85,
        'М21Б' => 0.85,
        //М20
        'М20' => 0.9,
        'M20' => 0.9,
        //М18
        'M18' => 0.9,
        'М18' => 0.9,
        //М16
        'M16' => 0.85,
        'М16' => 0.85,
        //М14
        'M14' => 0.8,
        'М14' => 0.8,
        //М12
        'M12' => 0.75,
        'М12' => 0.75,
        //Ж21Е
        'Ж21' => 1,
        'Ж21Е' => 1,
        'W21' => 1,
        'ЖЕ' => 1,
        'ЖE' => 1,
        'Ж21E' => 1,
        'W21E' => 1,
        'Ж21 Фин Е' => 1,
        'Женщины группа Е' => 1,
        //Ж21A
        'Ж21А' => 0.9,
        'Ж21A' => 0.9,
        'ЖA' => 0.9,
        'ЖА' => 0.9,
        'W21A' => 0.9,
        'Женщины группа А' => 0.9,
        //Ж21Б
        'Ж21Б' => 0.85,
        'Женщины группа В' => 0.85,
        'Ж21B' => 0.85,
        'W21B' => 0.85,
        'ЖB' => 0.85,
        'ЖВ' => 0.85,
        //Ж20
        'Ж20' => 0.9,
        'W20' => 0.9,
        //Ж18
        'Ж18' => 0.9,
        'W18' => 0.9,
        //Ж16
        'Ж16' => 0.85,
        'W16' => 0.85,
        //Ж14
        'Ж14' => 0.8,
        'W14' => 0.8,
        //Ж12
        'Ж12' => 0.75,
        'W12' => 0.75,
    ];

    public function getNameKey(): string
    {
        return 'app.cup.type.youth';
    }

    /**
     * @param CupEvent $cupEvent
     * @param CupGroup $mainGroup
     * @return Collection //array<int, CupEventPoint>
     */
    public function calculateEvent(CupEvent $cupEvent, CupGroup $mainGroup): Collection
    {
        $results = new Collection();
        $ageParticipants = $this->getGroupProtocolLines($cupEvent, $mainGroup);
        $ageParticipants = $ageParticipants->groupBy('distance_id');
        $mailGroups = $this->getEventGroups($mainGroup->male())->pluck('id');
        $eventDistances = $this->distanceService
            ->getCupEventDistancesByGroups($cupEvent, $mailGroups)
            ->keyBy('id')
        ;
        $ageParticipants = $ageParticipants->intersectByKeys($eventDistances);
        foreach ($ageParticipants as $distanceId => $groupProtocolLines) {
            $eventGroupResults = $this->calculateDistance($cupEvent, $distanceId);
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

    protected function calculateLines(CupEvent $cupEvent, Collection $protocolLines): Collection
    {
        $cupEventPointsList = Collection::make();
        $maxPoints = $cupEvent->points;

        $protocolLines = $protocolLines->sortByDesc(static fn (ProtocolLine $line) => $line->time ? $line->time->diffInSeconds() : 0);

        $first = true;
        // О уч. = К сор. х 500 х К гр. (3 х Т поб. / Т уч. ‑ 1), где:
        // К сор. – коэффициент соревнований (для соревнований класса «А» = 1, класса «В» = 0,9);
        // Т поб. – время победителя в группе (время спортсмена, учащегося Республики Беларусь, показавшего лучший результат);
        // Т уч. – результат участника;
        // К гр. – коэффициент группы, который равен:

        foreach ($protocolLines as $protocolLine) {
            /** @var ProtocolLine $protocolLine */
            $koef = self::EVENTS_GROUPS_KOEF[$protocolLine->distance->group->name] ?? 0;

            if ($first) {
                if ($protocolLine->person_id !== null) {
                    /** @var ProtocolLine $firstResult */
                    $firstResult = $protocolLines->first();
                    $firstResultSeconds = $firstResult->time ? $firstResult->time->secondsSinceMidnight() : 0;
                    $cupEventPoints = new CupEventPoint(
                        $cupEvent->id,
                        $protocolLine,
                        $firstResult->time === null ? 0 : (int)round($maxPoints * 1000 * $koef),//К сор. х 500 х К гр. (3 х Т поб. / Т уч. ‑ 1)
                    );
                    $first = false;
                } else {
                    $cupEventPoints = new CupEventPoint($cupEvent->id, $protocolLine, '-');
                }
            } else {
                if ($protocolLine->person_id === null) {
                    $points = '-';
                } elseif ($protocolLine->time !== null) {
                    $lineTime = $protocolLine->time->secondsSinceMidnight();
                    //К сор. х 500 х К гр. (3 х Т поб. / Т уч. ‑ 1)
                    $points = (int)round($maxPoints * 500 * $koef * (3 * ($firstResultSeconds ?? 0) / $lineTime - 1));
                    $points = $points < 0 ? 0 : $points;
                } else {
                    $points = 0;
                }
                $cupEventPoints = new CupEventPoint($cupEvent->id, $protocolLine, $points);
            }

            $cupEventPointsList->put($cupEventPoints->protocolLine->person_id, $cupEventPoints);
        }

        return $cupEventPointsList;
    }

    /**
     * @param CupEvent $cupEvent
     * @param int $distanceId
     * @return Collection
     */
    private function calculateDistance(CupEvent $cupEvent, int $distanceId): Collection
    {
        return $this->calculateLines(
            $cupEvent,
            $this->protocolLinesRepository->getCupEventDistanceProtocolLines($distanceId),
        );
    }
}
