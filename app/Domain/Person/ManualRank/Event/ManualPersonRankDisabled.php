<?php

declare(strict_types=1);

namespace App\Domain\Person\ManualRank\Event;

final readonly class ManualPersonRankDisabled
{
    public function __construct(public string $manualRankId, public int $personId)
    {
    }
}
