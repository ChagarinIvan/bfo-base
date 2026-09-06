<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Person\Citizenship;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Shared\Criteria;
use App\Infrastructure\Laravel\Eloquent\ProtocolLine\EloquentProtocolLinesRepository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

/**
 * Temporary legacy adapter for protocol-line operations that are not part of
 * the ProtocolLineRepository port yet.
 */
final readonly class ProtocolLinesRepository
{
    private EloquentProtocolLinesRepository $repository;

    public function __construct(private ConnectionInterface $db)
    {
        $this->repository = new EloquentProtocolLinesRepository();
    }

    public function byCriteria(Criteria $criteria): Collection
    {
        return $this->repository->byCriteria($criteria);
    }

    public function getCupEventProtocolLinesForPersonsCertainAge(
        CupEvent $cupEvent,
        ?int $startYear = null,
        ?int $finishYear = null,
        bool $withPayments = false,
        ?Collection $groups = null,
        bool $citizhenship = false,
    ): Collection {
        $protocolLinesQuery = ProtocolLine::selectRaw('protocol_lines.*')
            ->with(['person.club'])
            ->join('person', 'person.id', '=', 'protocol_lines.person_id')
            ->join('distances', 'distances.id', '=', 'protocol_lines.distance_id')
            ->where('protocol_lines.vk', false)
            ->where('distances.event_id', $cupEvent->event_id)
        ;

        if ($finishYear) {
            $protocolLinesQuery->where('person.birthday', '<=', "$finishYear-12-31");
        }

        if ($startYear) {
            $protocolLinesQuery->where('person.birthday', '>=', "$startYear-01-01");
        }

        if ($citizhenship) {
            $protocolLinesQuery->where('person.citizenship', Citizenship::BELARUS->value);
        }

        if ($withPayments) {
            $protocolLinesQuery
                ->addSelect('persons_payments.date')
                ->join('persons_payments', 'person.id', '=', 'persons_payments.person_id')
                ->where('persons_payments.year', '=', $cupEvent->cup->year)
                ->where('persons_payments.date', '<=', $cupEvent->event->date)
            ;
        }

        if ($groups instanceof Collection) {
            $protocolLinesQuery->whereIn('distances.group_id', $groups->pluck('id'));
        }

        return $protocolLinesQuery->get();
    }

    public function getCupEventGroupProtocolLinesForPersonsWithPayment(CupEvent $cupEvent, int $groupId): Collection
    {
        return ProtocolLine::selectRaw('protocol_lines.*, persons_payments.date')
            ->with(['person.club'])
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
            ->with(['person.club'])
            ->join('person', 'person.id', '=', 'protocol_lines.person_id')
            ->where('protocol_lines.vk', false)
            ->where('person.citizenship', Citizenship::BELARUS->value)
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
            ->join('person AS p', 'p.id', '=', 'pp.person_id')
            ->whereNull('pl.person_id')
            ->where('p.active', true)
            ->whereIn('pl.id', $linesIds)
            ->update(['pl.person_id' => new Expression('pp.person_id')])
        ;
    }
}
