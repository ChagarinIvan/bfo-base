<?php

namespace App\Repositories;

use App\Models\ProtocolLine;
use Illuminate\Database\ConnectionInterface;

class ProtocolLinesRepository
{
    private ConnectionInterface $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    public function getProtocolLine(int $id, array $with): ProtocolLine
    {
        $protocolLineQuery = ProtocolLine::where('id', $id);
        if (count($with) > 0) {
            $protocolLineQuery->with($with);
        }
        return $protocolLineQuery->first();
    }

    public function getLineForPersonOnEvent(int $personId, int $eventId): int
    {
        return (int)$this->db->table('protocol_lines', 'pl')
            ->join('distances AS d', 'd.id', '=', 'pl.distance_id')
            ->where('pl.person_id', $personId)
            ->where('d.event_id', $eventId)
            ->value('pl.id');
    }
}
