<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

final readonly class ViewPerson
{
    public function __construct(
        private string $id,
        private bool $includeProtocolLines = false,
    ) {
    }

    public function id(): int
    {
        return (int) $this->id;
    }

    public function includeProtocolLines(): bool
    {
        return $this->includeProtocolLines;
    }
}
