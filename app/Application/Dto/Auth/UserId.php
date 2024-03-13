<?php

declare(strict_types=1);

namespace App\Application\Dto\Auth;

final readonly class UserId
{
    public function __construct(
        public int $id,
    ) {
    }
}
