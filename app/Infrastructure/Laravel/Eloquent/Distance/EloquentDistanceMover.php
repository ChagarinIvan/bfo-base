<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Distance;

use App\Domain\Distance\Distance;
use App\Domain\Distance\DistanceMover;

final class EloquentDistanceMover implements DistanceMover
{
    public function moveFromGroupToGroup(int $sourceGroupId, int $targetGroupId): void
    {
        Distance::where('group_id', $sourceGroupId)->update(['group_id' => $targetGroupId]);
    }
}
