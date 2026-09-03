<?php

declare(strict_types=1);

namespace App\Domain\Person;

final readonly class PersonResources
{
    public function __construct(
        public bool $protocolLines = false,
        public bool $rankHistory = false,
    ) {
    }
}
