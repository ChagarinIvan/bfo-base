<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CupEvent;
use App\Repositories\DistanceRepository;
use Illuminate\Support\Collection;

class DistanceService
{
    private DistanceRepository $distanceRepository;

    public function __construct(DistanceRepository $distanceRepository)
    {
        $this->distanceRepository = $distanceRepository;
    }

    public function getCupEventDistancesByGroups(CupEvent $cupEvent, Collection $groups, Collection $groupNames): Collection
    {
        return $this->distanceRepository->getCupEventDistancesByGroups($cupEvent, $groups, $groupNames);
    }
}
