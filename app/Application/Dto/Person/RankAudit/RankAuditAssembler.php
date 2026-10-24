<?php

declare(strict_types=1);

namespace App\Application\Dto\Person\RankAudit;

use App\Application\Dto\Auth\AuthAssembler;
use App\Domain\Person\Audit\RankAuditEntry;
use App\Domain\Person\Audit\RankHistorySnapshot;
use App\Domain\Person\Audit\RankSnapshot;
use function array_map;

final readonly class RankAuditAssembler
{
    public function __construct(private AuthAssembler $auth)
    {
    }

    public function toDto(RankAuditEntry $entry): RankAuditDto
    {
        return new RankAuditDto(
            (string) $entry->id,
            $entry->personId,
            $entry->trigger,
            $this->auth->toImpressionDto($entry->created),
            $this->snapshot($entry->before),
            $this->snapshot($entry->after)
        );
    }

    /** @return array<string, mixed>|null */
    public function snapshot(?RankSnapshot $snapshot): ?array
    {
        if ($snapshot === null) {
            return null;
        }

        return [
            'current' => [
                'rankId' => $snapshot->current->rank->value,
                'startedOn' => $snapshot->current->startedOn?->toDateString(),
                'activatedOn' => $snapshot->current->activatedOn?->toDateString(),
                'finishedOn' => $snapshot->current->finishedOn?->toDateString(),
            ],
            'history' => array_map(static fn (RankHistorySnapshot $row): array => [
                'protocolLineId' => $row->protocolLineId, 'distanceId' => $row->distanceId,
                'eventId' => $row->eventId, 'competitionId' => $row->competitionId,
                'rankId' => $row->rank->value, 'changeType' => $row->changeType->value,
                'achievedOn' => $row->achievedOn->toDateString(), 'activatedOn' => $row->activatedOn?->toDateString(),
                'startedOn' => $row->startedOn->toDateString(), 'finishedOn' => $row->finishedOn?->toDateString(),
            ], $snapshot->history),
            'nextActivationOn' => $snapshot->nextActivationOn?->toDateString(),
        ];
    }
}
