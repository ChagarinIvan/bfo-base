<?php

declare(strict_types=1);

namespace App\Application\Service\Person\RankAudit;

use App\Domain\Shared\Criteria;

final readonly class ListRankAudit
{
    public function __construct(public int $personId)
    {
    }

    public function criteria(): Criteria
    {
        return new Criteria(['personId' => $this->personId]);
    }
}
