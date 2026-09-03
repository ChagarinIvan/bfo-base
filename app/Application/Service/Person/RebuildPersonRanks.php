<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Dto\Auth\UserId;

final readonly class RebuildPersonRanks
{
    public function __construct(
        public int $personId,
        public UserId $userId,
    ) {
    }
}
