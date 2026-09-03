<?php

declare(strict_types=1);

namespace App\Application\Dto\Club;

final readonly class ClubOptionDto
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }
}
