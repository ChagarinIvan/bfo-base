<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Domain\Person\PersonResources;

final readonly class ViewPerson
{
    public function __construct(
        private string $id,
        private bool $includeProtocolLines = false,
        private bool $includeRankHistory = false,
    ) {
    }

    public function id(): int
    {
        return (int) $this->id;
    }

    public function resources(): PersonResources
    {
        return new PersonResources(
            protocolLines: $this->includeProtocolLines,
            rankHistory: $this->includeRankHistory,
        );
    }
}
