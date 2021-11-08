<?php

namespace App\Models\Cups;

use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Group;
use App\Models\ProtocolLine;
use Illuminate\Support\Collection;

class YouthCupType extends AbstractCupType
{
    private const EVENTS_GROUPS_KOEF = [
        Group::M21E => 1,   //М21Е
        Group::M21A => 1,   //М21A
        Group::M21B => 0.9, //М21Б
        Group::M20 => 0.9,  //М20
        Group::M18 => 0.8,  //М18
        Group::M16 => 0.7,  //М16
        Group::M14 => 0.6,  //М14
        Group::M12 => 0.5,  //М12
        Group::W21E => 1,   //Ж21Е
        Group::W21A => 1,   //Ж21A
        Group::W21B => 0.9, //Ж21Б
        Group::W20 => 0.9,  //Ж20
        Group::W18 => 0.8,  //Ж18
        Group::W16 => 0.7,  //Ж16
        Group::W14 => 0.6,  //Ж14
        Group::W12 => 0.5,  //Ж12
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
     * @param Group $mainGroup
     * @return Collection //array<int, CupEventPoint>
     */
    public function calculateEvent(CupEvent $cupEvent, Group $mainGroup): Collection
    {
        $results = new Collection();
        $ageParticipants = $this->getGroupProtocolLines($cupEvent, $mainGroup);
        $ageParticipants = $ageParticipants->groupBy('distance_id');

        $groupsNames = $mainGroup->maleGroups();
        $eventGroupsIds = $this->groupsService->getGroups(array_keys(self::EVENTS_GROUPS_KOEF))->pluck('id');
        $eventDistances = $this->distanceService->getCupEventDistancesByGroups($cupEvent, $eventGroupsIds, $groupsNames)
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

    protected function getGroupProtocolLines(CupEvent $cupEvent, Group $group): Collection
    {
        $year = $cupEvent->cup->year;
        $startYear = $year - $group->years();
        $finishYear = $startYear + 1;

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
        return $this->groupsService->getGroups([
            Group::M18,
            Group::M16,
            Group::M14,
            Group::M12,
            Group::W18,
            Group::W16,
            Group::W14,
            Group::W12,
        ]);
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
