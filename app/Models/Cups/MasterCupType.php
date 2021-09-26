<?php

namespace App\Models\Cups;

use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Group;
use App\Models\ProtocolLine;
use App\Repositories\ProtocolLinesRepository;
use App\Services\DistanceService;
use App\Services\GroupsService;
use Illuminate\Support\Collection;

class MasterCupType extends AbstractCupType
{
    private Collection $eventGroupsIds;
    private ProtocolLinesRepository $protocolLinesRepository;
    private GroupsService $groupsService;
    private DistanceService $distanceService;

    public function __construct(
        ProtocolLinesRepository $protocolLinesRepository,
        GroupsService $groupsService,
        DistanceService $distanceService,
    ) {
        $this->protocolLinesRepository = $protocolLinesRepository;
        $this->groupsService = $groupsService;
        $this->distanceService = $distanceService;
        $this->eventGroupsIds = Collection::empty();
    }

    public function getId(): string
    {
        return CupType::MASTER;
    }

    public function getName(): string
    {
        return 'Ветеранский';
    }

    private function getEventGroupsIds(CupEvent $cupEvent): Collection
    {
        if ($this->eventGroupsIds->isEmpty()) {
            $this->eventGroupsIds = $cupEvent->cup->groups->pluck('id');
        }
        return $this->eventGroupsIds;
    }

    /**
     * @param CupEvent $cupEvent
     * @param Group $mainGroup
     * @return Collection //array<int, CupEventPoint>
     */
    public function calculateEvent(CupEvent $cupEvent, Group $mainGroup): Collection
    {
        $results = new Collection();
        $cupEventProtocolLines = $this->getGroupProtocolLines($cupEvent, $mainGroup);
        $eventGroupsId = $this->getEventGroupsIds($cupEvent);

        $groupsName = $mainGroup->maleGroups();
        $eventDistances = $this->distanceService->getCupEventDistancesByGroups($cupEvent, $eventGroupsId, $groupsName)
            ->pluck('id')
            ->toArray();

        $cupEventProtocolLines = $cupEventProtocolLines->filter(
            fn (ProtocolLine $protocolLine) => in_array($protocolLine->distance_id, $eventDistances, true)
        );

        $cupEventProtocolLines = $cupEventProtocolLines->groupBy('distance.group_id');
        $validGroups = $eventGroupsId->flip();
        $cupEventProtocolLines = $cupEventProtocolLines->intersectByKeys($validGroups);
        $groups = $this->groupsService->getCupEventGroups($cupEvent);
        $hasGroupOnEvent = $groups->pluck('id')->flip()->has($mainGroup->id);

        foreach ($cupEventProtocolLines as $groupId => $groupProtocolLines) {
            $needDivideGroup = !$hasGroupOnEvent && $mainGroup->isPreviousGroup($groupId);
            if ($needDivideGroup) {
                $eventGroupResults = $this->calculateLines($cupEvent, $groupProtocolLines);
            } else {
                $eventGroupResults = $this->calculateGroup($cupEvent, $groupId);
            }
            $results = $results->merge($eventGroupResults->intersectByKeys($groupProtocolLines->keyBy('person_id')));
        }

        return $results->sortByDesc(fn (CupEventPoint $cupEventResult) => $cupEventResult->points);
    }

    /**
     * @param CupEvent $cupEvent
     * @param int $groupId
     * @return Collection
     */
    private function calculateGroup(CupEvent $cupEvent, int $groupId): Collection
    {
        return $this->calculateLines(
            $cupEvent,
            $this->protocolLinesRepository->getCupEventGroupProtocolLinesForPersonsWithPayment($cupEvent, $groupId),
        );
    }

    protected function getGroupProtocolLines(CupEvent $cupEvent, Group $group): Collection
    {
        $year = $cupEvent->cup->year;
        $startYear = $year - $group->years();
        $finishYear = $startYear - 5;

        return $this->protocolLinesRepository->getCupEventProtocolLinesForPersonsCertainAge(
            $cupEvent,
            $finishYear,
            $startYear,
            true
        );
    }

    public function getCupGroups(Collection $groups): Collection
    {
        return $groups;
    }
}
