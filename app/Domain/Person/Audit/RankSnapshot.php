<?php

declare(strict_types=1);

namespace App\Domain\Person\Audit;

use App\Domain\Person\PersonRank;
use App\Domain\Person\PersonRankHistory;
use App\Domain\Person\PersonRankState;
use Carbon\Carbon;
use function array_all;
use function array_map;
use function count;
use function usort;

final readonly class RankSnapshot
{
    /** @var list<RankHistorySnapshot> */
    public array $history;

    /** @param list<PersonRankHistory> $history */
    public static function fromValues(PersonRank $current, array $history, ?Carbon $next): self
    {
        return new self($current, array_map(static fn (PersonRankHistory $row): RankHistorySnapshot => new RankHistorySnapshot(
            $row->protocol_line_id,
            $row->distance_id,
            $row->event_id,
            $row->competition_id,
            $row->rank,
            $row->change_type,
            $row->achieved_on,
            $row->activated_on,
            $row->started_on,
            $row->finished_on,
        ), $history), $next);
    }

    public static function fromState(PersonRankState $state): self
    {
        return self::fromValues($state->current, $state->history, $state->nextActivationOn);
    }

    /** @param list<RankHistorySnapshot> $history */
    public function __construct(public PersonRank $current, array $history, public ?Carbon $nextActivationOn)
    {
        usort($history, static fn (RankHistorySnapshot $left, RankHistorySnapshot $right): int => [$left->protocolLineId, $left->rank->value] <=> [$right->protocolLineId, $right->rank->value]);
        $this->history = $history;
    }

    public function currentEquals(self $other): bool
    {
        return $this->current->rank === $other->current->rank
            && $this->current->startedOn?->toDateString() === $other->current->startedOn?->toDateString()
            && $this->current->activatedOn?->toDateString() === $other->current->activatedOn?->toDateString()
            && $this->current->finishedOn?->toDateString() === $other->current->finishedOn?->toDateString();
    }

    public function historyEquals(self $other): bool
    {
        if (count($this->history) !== count($other->history)) {
            return false;
        }
        return array_all($this->history, static fn(RankHistorySnapshot $row, $index): bool => $row->equals($other->history[$index]));
    }

    public function equals(self $other): bool
    {
        return $this->currentEquals($other) && $this->historyEquals($other)
            && $this->nextActivationOn?->toDateString() === $other->nextActivationOn?->toDateString();
    }
}
