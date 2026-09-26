<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Distance;

use App\Domain\Distance\Distance;
use App\Domain\Distance\DistanceDeleter;

final readonly class EloquentDistanceDeleter implements DistanceDeleter
{
    public function deleteForEvent(int $eventId): void
    {
        Distance::query()->where('event_id', $eventId)->delete();
    }
}
