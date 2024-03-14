<?php

declare(strict_types=1);

namespace App\Application\Service\Competition;

final readonly class ViewCompetition
{
    public function __construct(private string $id)
    {
    }

    public function id(): int
    {
        return (int) $this->id;
    }
}
