<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPrompt;

use App\Application\Dto\Auth\UserId;

final readonly class ChangePersonPrompt
{
    public function __construct(
        public string $prompt,
        public int $personId,
        public UserId $userId,
    ) {
    }
}
