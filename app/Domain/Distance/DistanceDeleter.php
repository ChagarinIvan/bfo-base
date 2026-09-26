<?php

declare(strict_types=1);

namespace App\Domain\Distance;

interface DistanceDeleter
{
    public function deleteForEvent(int $eventId): void;
}
