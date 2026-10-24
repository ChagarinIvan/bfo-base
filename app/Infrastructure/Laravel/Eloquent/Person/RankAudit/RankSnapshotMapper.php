<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Person\RankAudit;

use App\Domain\Person\Audit\RankHistorySnapshot;
use App\Domain\Person\Audit\RankSnapshot;
use App\Domain\Person\PersonRank;
use App\Domain\Person\RankChangeType;
use App\Domain\Rank\Rank;
use Carbon\Carbon;
use function array_map;

final readonly class RankSnapshotMapper
{
    /** @param array<string, mixed>|null $data */
    public function fromStorage(?array $data): ?RankSnapshot
    {
        if ($data === null) {
            return null;
        }
        $current = $data['current'];

        return new RankSnapshot(
            new PersonRank(Rank::from($current['rankId']), $this->date($current['startedOn']), $this->date($current['activatedOn']), $this->date($current['finishedOn'])),
            array_map(fn (array $row): RankHistorySnapshot => new RankHistorySnapshot(
                $row['protocolLineId'],
                $row['distanceId'],
                $row['eventId'],
                $row['competitionId'],
                Rank::from($row['rankId']),
                RankChangeType::from($row['changeType']),
                Carbon::parse($row['achievedOn']),
                $this->date($row['activatedOn']),
                Carbon::parse($row['startedOn']),
                $this->date($row['finishedOn']),
            ), $data['history']),
            $this->date($data['nextActivationOn']),
        );
    }

    /** @return array<string, mixed>|null */
    public function toStorage(?RankSnapshot $snapshot): ?array
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

    private function date(?string $value): ?Carbon
    {
        return $value === null ? null : Carbon::parse($value);
    }
}
