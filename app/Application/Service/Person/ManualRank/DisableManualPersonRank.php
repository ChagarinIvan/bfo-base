<?php

declare(strict_types=1);
namespace App\Application\Service\Person\ManualRank;
use App\Application\Dto\Auth\UserId;
final readonly class DisableManualPersonRank
{
    public function __construct(public int $personId, public string $manualRankId, private UserId $userId)
    {
    }
    public function userId(): int
    {
        return $this->userId->id;
    }
}
