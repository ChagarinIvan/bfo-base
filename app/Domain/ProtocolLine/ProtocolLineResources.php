<?php

declare(strict_types=1);

namespace App\Domain\ProtocolLine;

final readonly class ProtocolLineResources
{
    public function __construct(
        public bool $withEvent = false,
        public bool $withCompetition = false,
        public bool $withClub = false,
    ) {
    }
}
