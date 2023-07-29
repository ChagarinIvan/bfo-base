<?php

namespace App\Models\Cups;

use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Group\CupGroup;
use App\Models\Group\CupGroupFactory;
use App\Models\Group\GroupAge;
use App\Models\Group\GroupMale;
use App\Models\ProtocolLine;
use Illuminate\Support\Collection;

/**
 * Юніёрскі
 *
 * Очки Кубка БФО среди юниоров начисляются спортсменам соответствующего
 * возраста по результатам соревнований в следующих группах с использованием
 * коэффициента группы:
 *  М21Е и Ж21Е – Кгр = 1,0
 *  М20 и Ж20 – Кгр = 0,9
 *  М21А и Ж21А – Кгр = 0,7
 *
 * Если дистанции групп М20 и Ж20 совмещены с группами М21Е и Ж21Е,
 * то очки начисляются только по результатам группы М21Е и Ж21Е.
 */
class JuniorCupType extends MasterCupType
{
    public const MEN_MAIN_GROUPS_NAMES = ['М20', 'M20'];
    public const MEN_SECOND_GROUPS_NAMES = ['М21А', 'М21A', 'M21A', 'М21 Фин А', 'МА', 'МA', 'Мужчины группа А',];

    public const WOMEN_MAIN_GROUPS_NAMES = ['Ж20', 'W20'];

    public const WOMEN_SECOND_GROUPS_NAMES = ['Ж21А', 'Ж21A', 'Ж21 Фин A', 'ЖA', 'ЖА', 'W21A', 'Женщины группа А',];

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
        'М21А' => 0.7,
        'М21A' => 0.7,
        'M21A' => 0.7,
        'М21 Фин А' => 0.7,
        'МА' => 0.7,
        'МA' => 0.7,
        'Мужчины группа А' => 1,
        //М20
        'М20' => 0.9,
        'M20' => 0.9,
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
        'Ж21А' => 0.7,
        'Ж21A' => 0.7,
        'ЖA' => 0.7,
        'ЖА' => 0.7,
        'W21A' => 0.7,
        'Женщины группа А' => 0.7,
        //Ж20
        'Ж20' => 0.9,
        'W20' => 0.9,
    ];

    public function getId(): string
    {
        return CupType::JUNIORS;
    }

    public function getNameKey(): string
    {
        return 'app.cup.type.junior';
    }

    public function getGroups(): Collection
    {
        return CupGroupFactory::getAgeTypeGroups([GroupAge::a20]);
    }

    protected function getGroupProtocolLines(CupEvent $cupEvent, CupGroup $group): Collection
    {
        $year = $cupEvent->cup->year;
        $startYear = $year - $group->age() ?->value ?? 0;
        $mainGroupsNames = $group->male() === GroupMale::Man ? self::MEN_MAIN_GROUPS_NAMES : self::WOMEN_MAIN_GROUPS_NAMES;
        $groups = $this->groupsService->getGroups($mainGroupsNames);
        $eliteGroupsNames = $group->male() === GroupMale::Man ? EliteCupType::MEN_GROUPS : EliteCupType::WOMEN_GROUPS;
        $eliteGroupsList = $this->groupsService->getGroups($eliteGroupsNames);

        if ($groups->isEmpty()) {
            $groups = $eliteGroupsList;
        } else {
            $differentGroupsNames = $group->male() === GroupMale::Man ? self::MEN_SECOND_GROUPS_NAMES : self::WOMEN_SECOND_GROUPS_NAMES;
            $differentGroupsList = $this->groupsService->getGroups($differentGroupsNames);;
            $groups = $groups->merge($eliteGroupsList);
            $groups = $groups->merge($differentGroupsList);
        }

        return $this->protocolLinesRepository
            ->getCupEventProtocolLinesForPersonsCertainAge(
                cupEvent: $cupEvent,
                finishYear: $startYear,
                withPayments: true,
                groups: $groups,
            )
        ;
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
        $eventGroupsId = $this->getEventGroups($mainGroup->male())->pluck('id');
        $eventDistances = $this->distanceService
            ->getCupEventDistancesByGroups($cupEvent, $eventGroupsId)
            ->keyBy('id')
        ;
        $ageParticipants = $ageParticipants->intersectByKeys($eventDistances);
        foreach ($ageParticipants as $distanceId => $groupProtocolLines) {
            $eventGroupResults = $this->calculateDistance($cupEvent, $distanceId);
            $results = $results->merge($eventGroupResults->intersectByKeys($groupProtocolLines->keyBy('person_id')));
        }

        return $results->sortByDesc(fn(CupEventPoint $cupEventResult) => $cupEventResult->points);
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

    protected function calculateLines(CupEvent $cupEvent, Collection $protocolLines): Collection
    {
        $cupEventPointsList = Collection::make();
        $maxPoints = $cupEvent->points;

        $protocolLines = $protocolLines->sortByDesc(fn(ProtocolLine $line) => $line->time ? $line->time->diffInSeconds() : 0);

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
                    $points = (int)round($maxPoints * 500 * $koef * (3 * $firstResultSeconds / $lineTime - 1));
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
}
