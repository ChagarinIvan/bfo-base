<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

final readonly class RebuildPersonRanks
{
    public function __construct(public int $personId)
    {
    }
}
