<?php

declare(strict_types=1);
namespace App\Application\Service\Person\ManualRank;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\ManualRank\ManualPersonRankDto;
use App\Domain\Person\ManualRank\ManualRankInput;
use App\Domain\Rank\Rank;
use Carbon\Carbon;
final readonly class AddManualPersonRank
{
    public function __construct(public int $personId, private ManualPersonRankDto $dto, private UserId $userId)
    {
    }
    public function input(): ManualRankInput
    {
        return new ManualRankInput(Rank::from($this->dto->rankId), Carbon::parse($this->dto->achievedOn)->startOfDay(), Carbon::parse($this->dto->activatedOn)->startOfDay());
    }
    public function userId(): int
    {
        return $this->userId->id;
    }
}
