<?php

declare(strict_types=1);

namespace App\Application\Dto\Distance;

use App\Domain\Distance\Distance;

final readonly class DistanceAssembler
{
    public function toViewDistanceDto(Distance $distance): ViewDistanceDto
    {
        return new ViewDistanceDto(
            id: (string) $distance->id,
            eventId: (string) $distance->event_id,
            groupName: $distance->group->name,
            length: $distance->length,
            points: $distance->points,
            disqual: (bool) $distance->disqual,
        );
    }
}
