<?php

declare(strict_types=1);

namespace App\Application\Dto\Person\RankAudit;

use App\Application\Dto\Auth\AuthAssembler;
use App\Domain\Person\Audit\RankAuditAction;

final readonly class RankAuditAssembler
{
    public function __construct(private AuthAssembler $auth)
    {
    }

    public function toDto(RankAuditAction $action): RankAuditDto
    {
        return new RankAuditDto(
            (string) $action->id,
            $action->personId,
            $action->trigger,
            $this->auth->toImpressionDto($action->created),
            new AppliedRankDto(
                $action->current->rank->value,
                $action->current->startedOn?->toDateString(),
                $action->current->activatedOn?->toDateString(),
                $action->current->finishedOn?->toDateString(),
                $action->nextActivationOn?->toDateString(),
            ),
        );
    }
}
