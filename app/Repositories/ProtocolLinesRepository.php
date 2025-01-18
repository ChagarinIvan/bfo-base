<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Person\Citizenship;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Shared\Criteria;
use App\Models\Year;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;
use function count;

// TODO replace me in integration
final readonly class ProtocolLinesRepository implements ProtocolLineRepository
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
        return $this->buildQuery($criteria)->get();
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

        if ($groups) {
            $protocolLinesQuery->whereIn('distances.group_id', $groups->pluck('id'));
        }

        return $protocolLinesQuery->get();
    }

    public function getCupEventGroupProtocolLinesForPersonsWithPayment(CupEvent $cupEvent, int $groupId): Collection
    {
        return ProtocolLine::selectRaw('protocol_lines.*, persons_payments.date')
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
            ->whereNull('pl.person_id')
            ->whereIn('pl.id', $linesIds)
            ->update(['pl.person_id' => new Expression('pp.person_id')])
        ;
    }

    public function getProtocolLines(int $personId, ?Year $year): Collection
    {
        $query = ProtocolLine::selectRaw('protocol_lines.*')
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

    public function lockOneByCriteria(Criteria $criteria): ?ProtocolLine
    {
        /** @var ProtocolLine|null $protocolLine */
        $protocolLine = $this
            ->buildQuery($criteria)
            ->lockForUpdate()
            ->first()
        ;

        return $protocolLine;
    }

    public function oneByCriteria(Criteria $criteria): ?ProtocolLine
    {
        /** @var ProtocolLine|null $protocolLine */
        $protocolLine = $this
            ->buildQuery($criteria)
            ->first()
        ;

        return $protocolLine;
    }

    public function update(ProtocolLine $protocolLine): void
    {
        $protocolLine->save();
    }

    private function buildQuery(Criteria $criteria): Builder
    {
        $query = ProtocolLine::select('protocol_lines.*');

        if ($criteria->hasParam('personId')) {
            $query->where('person_id', $criteria->param('personId'));
        }

        if ($criteria->hasParam('year')) {
            $query
                ->join('distances', 'distances.id', '=', 'protocol_lines.distance_id')
                ->join('events', 'events.id', '=', 'distances.event_id')
                ->where('events.date', 'LIKE', $criteria->param('year')->value . '-%')
            ;
        }

        if ($criteria->hasParam('eventId')) {
            $query
                ->join('distances AS d', 'd.id', '=', 'protocol_lines.distance_id')
                ->where('d.event_id', $criteria->param('eventId'))
            ;
        }

        if ($criteria->hasParam('distances')) {
            $query
                ->selectRaw('protocol_lines.*, max(persons_payments.date)')
                ->join('person', 'person.id', '=', 'protocol_lines.person_id')
                ->leftJoin('persons_payments', 'person.id', '=', 'persons_payments.person_id')
                ->where('protocol_lines.vk', false)
                ->whereIn('distance_id', $criteria->param('distances'))
                ->groupBy('protocol_lines.id')
            ;
        }

        if ($criteria->hasParam('paymentYear')) {
            $query
                ->where('persons_payments.year', '>=', $criteria->param('paymentYear'))
                ->where('persons_payments.date', '<=', $criteria->param('eventDate'))
            ;
        }

        return $query;
    }
}
