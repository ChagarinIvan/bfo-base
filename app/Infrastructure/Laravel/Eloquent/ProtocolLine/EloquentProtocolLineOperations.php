<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\ProtocolLine;

use App\Domain\Event\Event;
use App\Domain\ProtocolLine\ProtocolLineOperations;
use App\Domain\Rank\Rank;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Expression;

final readonly class EloquentProtocolLineOperations implements ProtocolLineOperations
{
    public function __construct(
        private ConnectionInterface $db,
    ) {
    }

    public function deleteEventLines(Event $event): void
    {
        $event->protocolLines()->delete();
    }

    /** @return list<int> */
    public function personIdsForEvent(Event $event): array
    {
        return $event->protocolLines()
            ->whereNotNull('person_id')
            ->distinct()
            ->pluck('person_id')
            ->map(static fn (int $personId): int => $personId)
            ->all()
        ;
    }

    public function fastIdentByEvent(Event $event): void
    {
        $this->identByEqualPreparedLine($event);
        $this->identByEqualPersonPrompt($event);
    }

    public function activateEventLines(Event $event): void
    {
        $this->db->table('protocol_lines AS pl')
            ->join('distances AS d', 'd.id', '=', 'pl.distance_id')
            ->join('events AS e', 'e.id', '=', 'd.event_id')
            ->join('protocol_lines AS previous_lines', 'previous_lines.person_id', '=', 'pl.person_id')
            ->join('distances AS previous_distances', 'previous_distances.id', '=', 'previous_lines.distance_id')
            ->join('events AS previous_events', 'previous_events.id', '=', 'previous_distances.event_id')
            ->where('d.event_id', $event->id)
            ->whereNotNull('pl.person_id')
            ->whereNull('pl.activate_rank')
            ->whereIn('pl.complete_rank', [Rank::CandidateMaster->label(), Rank::MasterOfSport->label()])
            ->whereColumn('previous_lines.complete_rank', 'pl.complete_rank')
            ->whereNotNull('previous_lines.activate_rank')
            ->whereColumn('previous_events.date', '<', 'e.date')
            ->update(['pl.activate_rank' => new Expression('e.date')])
        ;
    }

    public function identByEqualPreparedLine(Event $event): void
    {
        $this->db->table('protocol_lines AS pls')
            ->join('distances AS d', 'd.id', '=', 'pls.distance_id')
            ->join('protocol_lines AS plj', 'plj.prepared_line', '=', 'pls.prepared_line')
            ->whereNull('pls.person_id')
            ->whereNotNull('plj.person_id')
            ->where('d.event_id', $event->id)
            ->update(['pls.person_id' => new Expression('plj.person_id')])
        ;
    }

    public function identByEqualPersonPrompt(Event $event): void
    {
        $this->db->table('protocol_lines AS pl')
            ->join('distances AS d', 'd.id', '=', 'pl.distance_id')
            ->join('persons_prompt AS pp', 'pl.prepared_line', '=', 'pp.prompt')
            ->join('person AS p', 'p.id', '=', 'pp.person_id')
            ->whereNull('pl.person_id')
            ->where('p.active', true)
            ->where('d.event_id', $event->id)
            ->update(['pl.person_id' => new Expression('pp.person_id')])
        ;
    }
}
