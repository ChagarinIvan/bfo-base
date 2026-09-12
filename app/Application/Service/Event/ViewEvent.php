<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Domain\Event\EventResources;

final readonly class ViewEvent
{
    public function __construct(
        private string $id,
    )
    {
    }

    public function id(): int
    {
        return (int) $this->id;
    }

    public function resources(): EventResources
    {
        return new EventResources();
    }
}
