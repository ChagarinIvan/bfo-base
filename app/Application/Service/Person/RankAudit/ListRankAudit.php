<?php

declare(strict_types=1);

namespace App\Application\Service\Person\RankAudit;

use App\Domain\Shared\Criteria;

final class ListRankAudit
{
    public Criteria $criteria {
        get => new Criteria(['personId' => $this->personId]);
    }
    public function __construct(public readonly int $personId)
    {
    }
}
