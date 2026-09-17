<?php

declare(strict_types=1);

namespace App\Application\Service\RankCheck;

final readonly class ListRankCheckRows
{
    public function __construct(public int $rankCheckId)
    {
    }
}
