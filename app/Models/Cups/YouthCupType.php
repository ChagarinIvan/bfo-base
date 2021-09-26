<?php

namespace App\Models\Cups;

use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Group;
use App\Models\ProtocolLine;
use App\Repositories\ProtocolLinesRepository;
use App\Services\DistanceService;
use Illuminate\Support\Collection;

class YouthCupType extends AbstractCupType
{
    private const EVENTS_GROUPS_KOEF = [
        7  => 1,   //М21Е
        8  => 1,   //М21A
        9  => 0.9, //М21Б
        6  => 0.9, //М20
        5  => 0.8, //М18
        4  => 0.7, //М16
        3  => 0.6, //М14
        2  => 0.5, //М12
        28 => 1,   //Ж21Е
        29 => 1,   //Ж21A
        30 => 0.9, //Ж21Б
        27 => 0.9, //Ж20
        26 => 0.8, //Ж18
        25 => 0.7, //Ж16
        24 => 0.6, //Ж14
        23 => 0.5, //Ж12
    ];

    private ProtocolLinesRepository $protocolLinesRepository;
    private DistanceService $distanceService;
    private Collection $eventDistances;

    public function __construct(
        ProtocolLinesRepository $protocolLinesRepository,
        DistanceService         $distanceService,
    ) {
        $this->protocolLinesRepository = $protocolLinesRepository;
        $this->distanceService = $distanceService;
        $this->eventDistances = Collection::empty();
    }

    public function getId(): string
    {
        return CupType::YOUTH;
    }

    public function getName(): string
    {
        return 'Юношеский';
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
        $eventGroupsIds = Collection::make(self::EVENTS_GROUPS_KOEF);
        $this->eventDistances = $this->distanceService->getCupEventDistancesByGroups($cupEvent, $eventGroupsIds->keys(), $groupsNames);
        $this->eventDistances = $this->eventDistances->keyBy('id');

        $ageParticipants = $ageParticipants->intersectByKeys($this->eventDistances);

        foreach ($ageParticipants as $distanceId => $groupProtocolLines) {
            $eventGroupResults = $this->calculateDistance($cupEvent, $distanceId, );
            $results = $results->merge($eventGroupResults->intersectByKeys($groupProtocolLines->keyBy('person_id')));
        }

        return $results->sortByDesc(fn (CupEventPoint $cupEventResult) => $cupEventResult->points);
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

        $protocolLines = $protocolLines->sortByDesc(fn (ProtocolLine $line) => $line->time ? $line->time->diffInSeconds() : 0);

        $first = true;
        // О уч. = К сор. х 500 х К гр. (3 х Т поб. / Т уч. ‑ 1), где:
        // К сор. – коэффициент соревнований (для соревнований класса «А» = 1, класса «В» = 0,9);
        // Т поб. – время победителя в группе (время спортсмена, учащегося Республики Беларусь, показавшего лучший результат);
        // Т уч. – результат участника;
        // К гр. – коэффициент группы, который равен:


        foreach ($protocolLines as $protocolLine) {
            /** @var ProtocolLine $protocolLine */
            $koef = self::EVENTS_GROUPS_KOEF[$protocolLine->distance->group_id] ?? 0;

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

    public function getCupGroups(Collection $groups): Collection
    {
        return $groups;
    }

    public function getCupEventParticipatesCount(CupEvent $cupEvent): int
    {
        $groups = $cupEvent->cup->getGroups();
        $lines = Collection::empty();

        foreach ($groups as $group) {
            $lines = $lines->merge($this->getGroupProtocolLines($cupEvent, $group));
        }

        return $lines->count();
    }
}
