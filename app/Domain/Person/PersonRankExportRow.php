<?php

declare(strict_types=1);

namespace App\Domain\Person;

final readonly class PersonRankExportRow
{
    public function __construct(
        public string $lastname,
        public string $firstname,
        public string $birthYear,
        public string $rank,
    ) {
    }
}
