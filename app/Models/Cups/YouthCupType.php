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

class YouthCupType extends MasterCupType
{
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
        'М21А' => 1,
        'М21A' => 1,
        'M21A' => 1,
        'М21 Фин А' => 1,
        'МА' => 1,
        'МA' => 1,
        'Мужчины группа А' => 1,
        //М21Б
        'Мужчины группа В' => 0.9,
        'МB' => 0.9,
        'M21B' => 0.9,
        'МВ' => 0.9,
        'М21B' => 0.9,
        'М21Б' => 0.9,
        //М20
        'М20' => 0.9,
        'M20' => 0.9,
        //М18
        'M18' => 0.8,
        'М18' => 0.8,
        //М16
        'M16' => 0.7,
        'М16' => 0.7,
        //М14
        'M14' => 0.6,
        'М14' => 0.6,
        //М12
        'M12' => 0.5,
        'М12' => 0.5,
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
        'Ж21А' => 1,
        'Ж21A' => 1,
        'ЖA' => 1,
        'ЖА' => 1,
        'W21A' => 1,
        'Женщины группа А' => 1,
        //Ж21Б
        'Ж21Б' => 0.9,
        'Женщины группа В' => 0.9,
        'Ж21B' => 0.9,
        'W21B' => 0.9,
        'ЖB' => 0.9,
        'ЖВ' => 0.9,
        //Ж20
        'Ж20' => 0.9,
        'W20' => 0.9,
        //Ж18
        'Ж18' => 0.8,
        'W18' => 0.8,
        //Ж16
        'Ж16' => 0.7,
        'W16' => 0.7,
        //Ж14
        'Ж14' => 0.6,
        'W14' => 0.6,
        //Ж12
        'Ж12' => 0.5,
        'W12' => 0.5,
    ];

    protected const GROUPS_MAP = [
        'M_12' => ['M12', 'М12'],
        'M_14' => ['M14', 'М14'],
        'M_16' => ['M16', 'М16'],
        'M_18' => ['M18', 'М18'],
        'M_20' => ['M20', 'М20'],
        'M_21' => ['M21', 'М21'],
        'W_12' => ['Ж12', 'W12'],
        'W_14' => ['Ж14', 'W14'],
        'W_16' => ['Ж16', 'W16'],
        'W_18' => ['Ж18', 'W18'],
        'W_20' => ['Ж20', 'W20'],
        'W_21' => ['Ж21', 'W21'],
    ];

    public function getId(): string
    {
        return CupType::YOUTH;
    }

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

        $eventGroupsId = $this->getEventGroups($mainGroup->male())->pluck('id');
        $eventDistances = $this->distanceService->getCupEventDistancesByGroups($cupEvent, $eventGroupsId)
            ->keyBy('id');

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

    protected function getGroupProtocolLines(CupEvent $cupEvent, CupGroup $group): Collection
    {
        $year = $cupEvent->cup->year;
        $startYear = $year - $group->age() ?->value ?? 0;
        $finishYear = $group->equal(CupGroup::create(GroupMale::Man, GroupAge::a12))
            ? $year
            : $startYear + 1;

        return $this->protocolLinesRepository->getCupEventProtocolLinesForPersonsCertainAge(
            $cupEvent,
            $startYear,
            $finishYear
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

    public function getGroups(): Collection
    {
        return CupGroupFactory::getAgeTypeGroups([GroupAge::a12, GroupAge::a14, GroupAge::a16, GroupAge::a18]);
    }

    public function getCalculatedGroups(): Collection
    {
        return CupGroupFactory::getAgeTypeGroups([GroupAge::a12, GroupAge::a14, GroupAge::a16, GroupAge::a18, GroupAge::a20, GroupAge::a21]);
    }

    public function getCupEventParticipatesCount(CupEvent $cupEvent): int
    {
        $groups = $this->getGroups();
        $lines = Collection::empty();

        foreach ($groups as $group) {
            $lines = $lines->merge($this->getGroupProtocolLines($cupEvent, $group));
        }

        return $lines->unique()->count();
    }
}
