<?php

declare(strict_types=1);
namespace App\Domain\Person\ManualRank;

use App\Domain\Person\ManualRank\Exception\InvalidManualRank;
use App\Domain\Rank\Rank;
use Carbon\Carbon;

final readonly class ManualRankInput
{
    public function __construct(public Rank $rank, public Carbon $achievedOn, public Carbon $activatedOn)
    {
        if ($rank === Rank::WithoutRank || $activatedOn < $achievedOn) {
            throw new InvalidManualRank();
        }
    }
}
