<?php

namespace App\Models\Cups;

use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Group;
use App\Repositories\DistanceRepository;
use App\Repositories\GroupsRepository;
use App\Repositories\ProtocolLinesRepository;
use Illuminate\Support\Collection;

class EliteCupType extends AbstractCupType
{
    protected DistanceRepository $distanceRepository;
    protected ProtocolLinesRepository $protocolLinesRepository;
    protected GroupsRepository $groupsRepository;

    public function __construct(
        DistanceRepository $distanceRepository,
        ProtocolLinesRepository $protocolLinesRepository,
        GroupsRepository $groupsRepository,
    ) {
        $this->distanceRepository = $distanceRepository;
        $this->protocolLinesRepository = $protocolLinesRepository;
        $this->groupsRepository = $groupsRepository;
    }

    public function getId(): string
    {
        return CupType::ELITE;
    }

    public function getName(): string
    {
        return 'Элитный';
    }

    /**
     * @param CupEvent $cupEvent
     * @param Group $mainGroup
     * @return Collection //array<int, CupEventPoint>
     */
    public function calculateEvent(CupEvent $cupEvent, Group $mainGroup): Collection
    {
        $cupEventProtocolLines = $this->getGroupProtocolLines($cupEvent, $mainGroup);
        $results = $this->calculateLines($cupEvent, $cupEventProtocolLines);

        return $results->sortByDesc(fn (CupEventPoint $cupEventResult) => $cupEventResult->points);
    }

    protected function getGroupProtocolLines(CupEvent $cupEvent, Group $group): Collection
    {
        $mainDistance = $this->distanceRepository->findDistance($group->id, $cupEvent->event_id);
        $equalDistances = $this->distanceRepository->getEqualDistances($mainDistance);
        $distances = $equalDistances->add($mainDistance);
        return $this->protocolLinesRepository->getCupEventDistancesProtocolLines($distances, $cupEvent);
    }

    public function getCupGroups(Collection $groups): Collection
    {
        return $groups;
    }
}
