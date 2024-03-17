<?php

declare(strict_types=1);

namespace App\Domain\Person;

use Carbon\Carbon;

final readonly class PersonInfo
{
    public function __construct(
        public string $firstname,
        public string $lastname,
        public ?Carbon $birthday,
        public ?int $clubId,
    ) {
    }
}
