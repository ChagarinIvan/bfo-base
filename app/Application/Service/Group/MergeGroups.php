<?php

declare(strict_types=1);

namespace App\Application\Service\Group;

use App\Application\Dto\Auth\UserId;

final readonly class MergeGroups
{
    public function __construct(
        private string $sourceId,
        private string $targetId,
        private UserId $userId,
    ) {
    }

    public function sourceId(): int
    {
        return (int) $this->sourceId;
    }

    public function targetId(): int
    {
        return (int) $this->targetId;
    }

    public function userId(): int
    {
        return $this->userId->id;
    }
}
