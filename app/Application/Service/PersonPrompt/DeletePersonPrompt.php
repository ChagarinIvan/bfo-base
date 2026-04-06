<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPrompt;

final readonly class DeletePersonPrompt
{
    public function __construct(
        private string $id,
    ) {
    }

    public function id(): int
    {
        return (int) $this->id;
    }
}
