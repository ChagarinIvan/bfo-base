<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

final readonly class ViewCupGroupDto
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }
}
