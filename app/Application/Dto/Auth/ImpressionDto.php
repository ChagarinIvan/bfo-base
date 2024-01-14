<?php

declare(strict_types=1);

namespace App\Application\Dto\Auth;

final readonly class ImpressionDto
{
    public function __construct(
        public string $by,
        public string $at,
    ) {
    }
}
