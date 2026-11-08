<?php

declare(strict_types=1);

namespace App\Application\Service\Person\ManualRank;

use App\Application\Dto\Auth\UserId;

final class DisableManualPersonRank
{
    public int $userId {
        get => $this->actor->id;
    }

    public function __construct(
        public readonly int $personId,
        public readonly string $manualRankId,
        private readonly UserId $actor,
    ) {
    }
}
