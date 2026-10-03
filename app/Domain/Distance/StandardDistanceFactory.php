<?php

declare(strict_types=1);

namespace App\Domain\Distance;

final readonly class StandardDistanceFactory implements DistanceFactory
{
    public function create(DistanceInput $input): Distance
    {
        $distance = new Distance();
        $distance->event_id = $input->eventId;
        $distance->group_id = $input->groupId;
        $distance->length = $input->length;
        $distance->points = $input->points;

        return $distance;
    }
}
