<?php

declare(strict_types=1);

namespace App\Application\Service\RankCheck;

use App\Domain\RankCheck\RankCheckStatus;
use App\Domain\Shared\Criteria;

final readonly class ListRankCheckRows
{
    public function __construct(public int $rankCheckId)
    {
    }

    public function criteria(): Criteria
    {
        return new Criteria([
            'rankCheckId' => $this->rankCheckId,
            'status' => RankCheckStatus::Ready->value,
        ]);
    }
}
