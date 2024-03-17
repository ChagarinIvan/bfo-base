<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

final readonly class ViewEvent
{
    public function __construct(private string $id)
    {
    }

    public function id(): int
    {
        return (int) $this->id;
    }
}
