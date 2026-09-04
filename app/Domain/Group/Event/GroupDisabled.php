<?php

declare(strict_types=1);

namespace App\Domain\Group\Event;

use App\Domain\Group\Group;
use App\Domain\Shared\AggregatedEvent;

final readonly class GroupDisabled extends AggregatedEvent
{
    public function __construct(public Group $group)
    {
    }
}
