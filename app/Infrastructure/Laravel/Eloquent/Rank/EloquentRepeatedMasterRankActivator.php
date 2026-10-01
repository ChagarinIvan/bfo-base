<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Rank;

use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use App\Domain\Rank\RepeatedMasterRankActivator;
use Closure;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

final readonly class EloquentRepeatedMasterRankActivator implements RepeatedMasterRankActivator
{
    public function activate(Collection $lines, bool $dryRun = false, ?Closure $beforeSave = null): array
    {
        $repeats = ProtocolLine::query()
            ->select('protocol_lines.*')
            ->addSelect('current_events.date as activation_event_date')
            ->join('distances as current_distances', 'current_distances.id', '=', 'protocol_lines.distance_id')
            ->join('events as current_events', 'current_events.id', '=', 'current_distances.event_id')
            ->whereKey($lines->pluck('id')->all())
            ->whereNotNull('protocol_lines.person_id')
            ->whereNull('protocol_lines.activate_rank')
            ->whereIn('protocol_lines.complete_rank', [Rank::CandidateMaster->label(), Rank::MasterOfSport->label()])
            ->whereExists(static function (QueryBuilder $query): void {
                $query
                    ->selectRaw('1')
                    ->from('protocol_lines as previous_lines')
                    ->join('distances as previous_distances', 'previous_distances.id', '=', 'previous_lines.distance_id')
                    ->join('events as previous_events', 'previous_events.id', '=', 'previous_distances.event_id')
                    ->whereColumn('previous_lines.person_id', 'protocol_lines.person_id')
                    ->whereColumn('previous_lines.complete_rank', 'protocol_lines.complete_rank')
                    ->whereNotNull('previous_lines.activate_rank')
                    ->whereColumn('previous_lines.id', '!=', 'protocol_lines.id')
                    ->whereColumn('previous_events.date', '<', 'current_events.date');
            })
            ->get();
        $ids = $repeats->pluck('id')->all();

        if (!$dryRun) {
            foreach ($repeats as $line) {
                $beforeSave?->__invoke();
                $line->setAttribute('activate_rank', $line->getAttribute('activation_event_date'));
                $line->save();
            }
        }

        return $ids;
    }

    public function activateForPersonInEvent(int $personId, int $eventId, Closure $beforeSave): void
    {
        $lines = ProtocolLine::query()
            ->join('distances', 'distances.id', '=', 'protocol_lines.distance_id')
            ->where('distances.event_id', $eventId)
            ->where('protocol_lines.person_id', $personId)
            ->select('protocol_lines.*')
            ->get();

        $this->activate($lines, false, $beforeSave);
    }
}
