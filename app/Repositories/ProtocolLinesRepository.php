<?php

namespace App\Repositories;

use App\Models\CupEvent;
use App\Models\Distance;
use App\Models\ProtocolLine;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

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

    /**
     * Идёт проверка на оплату взноса в федерацию
     *
     * @param Collection|Distance[] $distances
     * @param CupEvent $cupEvent
     * @return Collection
     */
    public function getCupEventDistancesProtocolLines(Collection|array $distances, CupEvent $cupEvent): Collection
    {
        return ProtocolLine::selectRaw(new Expression('`protocol_lines`.*, `persons_payments`.`date`'))
            ->join('person', 'person.id', '=', 'protocol_lines.person_id')
            ->join('persons_payments', 'person.id', '=', 'persons_payments.person_id')
            ->where('persons_payments.year', '=', $cupEvent->cup->year)
            ->where('persons_payments.date', '<=', $cupEvent->event->date)
            ->whereIn('distance_id', $distances->pluck('id')->unique())
            ->havingRaw(new Expression("`persons_payments`.`date` <= '{$cupEvent->event->date}'"))
            ->get();
    }

    public function getCupEventProtocolLinesForPersonsCertainAge(
        CupEvent $cupEvent,
        string $startYear,
        string $finishYear,
        bool $withPayments = false,
    ): Collection {
        $protocolLinesQuery = ProtocolLine::selectRaw(new Expression('protocol_lines.*'))
            ->join('person', 'person.id', '=', 'protocol_lines.person_id')
            ->join('distances', 'distances.id', '=', 'protocol_lines.distance_id')
            ->where('person.birthday', '<=', "{$finishYear}-01-01")
            ->where('person.birthday', '>=', "{$startYear}-01-01")
            ->where('distances.event_id', $cupEvent->event_id);

        if ($withPayments) {
            $protocolLinesQuery->addSelect('persons_payments.date')
                ->join('persons_payments', 'person.id', '=', 'persons_payments.person_id')
                ->where('persons_payments.year', '=', $cupEvent->cup->year)
                ->havingRaw(new Expression("`persons_payments`.`date` <= '{$cupEvent->event->date}'"));
        }

        return $protocolLinesQuery->get();
    }

    public function getCupEventGroupProtocolLinesForPersonsWithPayment(CupEvent $cupEvent, int $groupId): Collection
    {
        return ProtocolLine::selectRaw(new Expression('protocol_lines.*, persons_payments.date'))
            ->join('person', 'person.id', '=', 'protocol_lines.person_id')
            ->join('persons_payments', 'person.id', '=', 'persons_payments.person_id')
            ->join('distances', 'distances.id', '=', 'protocol_lines.distance_id')
            ->where('persons_payments.year', $cupEvent->cup->year)
            ->where('distances.event_id', $cupEvent->event_id)
            ->where('distances.group_id', $groupId)
            ->havingRaw(new Expression("persons_payments.date <= '{$cupEvent->event->date}'"))
            ->get();
    }

    public function getCupEventDistanceProtocolLines(int $distanceId): Collection
    {
        return ProtocolLine::where('protocol_lines.distance_id', $distanceId)
            ->get();
    }
}
