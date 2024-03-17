<?php

declare(strict_types=1);

namespace App\Domain\Person\Factory;

use App\Domain\Person\PersonInfo;

final readonly class PersonInput
{
    public function __construct(
        public PersonInfo $info,
        public bool $fromBase,
        public int $userId,
    ) {
    }
}
