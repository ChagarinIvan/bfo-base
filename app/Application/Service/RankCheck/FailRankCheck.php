<?php

declare(strict_types=1);

namespace App\Application\Service\RankCheck;

final readonly class FailRankCheck
{
    public function __construct(public int $id)
    {
    }
}
