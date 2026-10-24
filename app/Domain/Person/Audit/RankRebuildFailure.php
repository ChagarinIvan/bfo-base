<?php

declare(strict_types=1);

namespace App\Domain\Person\Audit;

use RuntimeException;
use Throwable;

final class RankRebuildFailure extends RuntimeException
{
    public function __construct(public readonly RankAuditEntry $entry, Throwable $previous)
    {
        parent::__construct('Rank processing failed.', 0, $previous);
    }
}
