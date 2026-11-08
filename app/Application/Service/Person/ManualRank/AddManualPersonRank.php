<?php

declare(strict_types=1);

namespace App\Application\Service\Person\ManualRank;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\ManualRank\ManualPersonRankDto;
use App\Domain\Person\ManualRank\ManualRankInput;
use App\Domain\Rank\Rank;
use Carbon\Carbon;

final class AddManualPersonRank
{
    public ManualRankInput $input {
        get => new ManualRankInput(
            Rank::from($this->dto->rankId),
            Carbon::parse($this->dto->achievedOn)->startOfDay(),
            Carbon::parse($this->dto->activatedOn)->startOfDay(),
        );
    }

    public int $userId {
        get => $this->actor->id;
    }

    public function __construct(
        public readonly int $personId,
        private readonly ManualPersonRankDto $dto,
        private readonly UserId $actor,
    ) {
    }
}
