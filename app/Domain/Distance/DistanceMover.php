<?php

declare(strict_types=1);

namespace App\Domain\Distance;

interface DistanceMover
{
    public function moveFromGroupToGroup(int $sourceGroupId, int $targetGroupId): void;
}
