<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Shared\Criteria;
use App\Models\CupEvent;
use App\Models\Distance;
use App\Models\ProtocolLine;
use App\Models\Year;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;
use function count;

final readonly class ProtocolLinesRepository
{
    public function __construct(private ConnectionInterface $db)
    {
    }

    public function byId(int $id, array $with = []): ?ProtocolLine
    {
        $protocolLineQuery = ProtocolLine::where('id', $id);

        if (count($with) > 0) {
            $protocolLineQuery->with($with);
        }
        return $protocolLineQuery->first();
    }

    public function getLineForPersonOnEvent(int $personId, int $eventId): int
    {
        return (int)$this->db
            ->table('protocol_lines', 'pl')
            ->join('distances AS d', 'd.id', '=', 'pl.distance_id')
            ->where('pl.person_id', $personId)
            ->where('d.event_id', $eventId)
            ->value('pl.id')
        ;
    }

    public function byCriteria(Criteria $criteria): Collection
    {
        $query = ProtocolLine::selectRaw('protocol_lines.*, persons_payments.date')
            ->join('person', 'person.id', '=', 'protocol_lines.person_id')
            ->leftJoin('persons_payments', 'person.id', '=', 'persons_payments.person_id')
            ->where('protocol_lines.vk', false)
            ->whereIn('distance_id', $criteria->param('distances'))
        ;

        if ($criteria->hasParam('paymentYear')) {
            $query
                ->where('persons_payments.year', '>=', $criteria->param('paymentYear'))
                ->where('persons_payments.date', '<=', $criteria->param('eventDate'))
            ;
        }

        return $query->get();
    }

    public function getCupEventProtocolLinesForPersonsCertainAge(
        CupEvent $cupEvent,
        ?string $startYear = null,
        ?string $finishYear = null,
        bool $withPayments = false,
        ?Collection $groups = null,
    ): Collection {
        $protocolLinesQuery = ProtocolLine::selectRaw(new Expression('protocol_lines.*'))
            ->join('person', 'person.id', '=', 'protocol_lines.person_id')
            ->join('distances', 'distances.id', '=', 'protocol_lines.distance_id')
            ->where('protocol_lines.vk', false)
            ->where('distances.event_id', $cupEvent->event_id)
        ;

        if ($finishYear) {
            $protocolLinesQuery->where('person.birthday', '<=', "$finishYear-01-01");
        }

        if ($startYear) {
            $protocolLinesQuery->where('person.birthday', '>=', "$startYear-01-01");
        }

        if ($withPayments) {
            $protocolLinesQuery
                ->addSelect('persons_payments.date')
                ->join('persons_payments', 'person.id', '=', 'persons_payments.person_id')
                ->where('persons_payments.year', '=', $cupEvent->cup->year)
                ->havingRaw('persons_payments.date <= ?', [$cupEvent->event->date])
            ;
        }

        if ($groups) {
            $protocolLinesQuery->whereIn('distances.group_id', $groups->pluck('id'));
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
            ->havingRaw('persons_payments.date <= ?', [$cupEvent->event->date])
            ->get()
        ;
    }

    public function getCupEventDistanceProtocolLines(int $distanceId): Collection
    {
        return ProtocolLine::where('protocol_lines.distance_id', $distanceId)
            ->where('protocol_lines.vk', false)
            ->get()
        ;
    }

    public function identByEqualPreparedLine(Collection $linesIds): void
    {
        $this->db->table('protocol_lines', 'pls')
            ->join('protocol_lines AS plj', 'plj.prepared_line', '=', 'pls.prepared_line')
            ->whereNull('pls.person_id')
            ->whereNotNull('plj.person_id')
            ->whereIn('pls.id', $linesIds)
            ->update(['pls.person_id' => new Expression('plj.person_id')])
        ;
    }

    public function identByEqualPersonPrompt(Collection $linesIds): void
    {
        $this->db->table('protocol_lines', 'pl')
            ->join('persons_prompt AS pp', 'pl.prepared_line', '=', 'pp.prompt')
            ->whereNull('pl.person_id')
            ->whereIn('pl.id', $linesIds)
            ->update(['pl.person_id' => new Expression('pp.person_id')])
        ;
    }

    public function getProtocolLines(int $personId, ?Year $year): Collection
    {
        $query = ProtocolLine::selectRaw(new Expression('protocol_lines.*'))
            ->join('distances', 'distances.id', '=', 'protocol_lines.distance_id')
            ->join('events', 'events.id', '=', 'distances.event_id')
            ->where('protocol_lines.person_id', $personId)
            ->orderBy('events.date')
        ;

        if ($year) {
            $query->where('events.date', 'LIKE', "$year->value-%");
        }

        return $query->get();
    }
}
