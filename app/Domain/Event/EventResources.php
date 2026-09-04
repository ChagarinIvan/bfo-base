<?php

declare(strict_types=1);

namespace App\Domain\Event;

final readonly class EventResources
{
    public function __construct(public bool $competitionName = false)
    {
    }
}
