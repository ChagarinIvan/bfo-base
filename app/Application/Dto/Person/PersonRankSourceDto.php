<?php

declare(strict_types=1);

namespace App\Application\Dto\Person;

final readonly class PersonRankSourceDto
{
    public function __construct(
        public int $personId,
        public int $protocolLineId,
    ) {
    }
}
