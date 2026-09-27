<?php

declare(strict_types=1);

namespace App\Application\Service\RankCheck;

use App\Domain\Shared\Criteria;

final readonly class ListRankChecks
{
    public function criteria(): Criteria
    {
        return Criteria::empty();
    }
}
