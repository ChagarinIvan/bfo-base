<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Person\Citizenship;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Shared\Criteria;
use App\Infrastructure\Laravel\Eloquent\ProtocolLine\EloquentProtocolLinesRepository;
use Illuminate\Support\Collection;

/**
 * Temporary legacy adapter for protocol-line operations that are not part of
 * the ProtocolLineRepository port yet.
 */
final readonly class ProtocolLinesRepository
{
    private EloquentProtocolLinesRepository $repository;

    public function __construct()
    {
        $this->repository = new EloquentProtocolLinesRepository();
    }

    public function byCriteria(Criteria $criteria): Collection
    {
        return $this->repository->byCriteria($criteria, ['distance.group', 'person']);
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
            ->with(['person.club', 'distance.group'])
            ->join('person', 'person.id', '=', 'protocol_lines.person_id')
            ->join('distances', 'distances.id', '=', 'protocol_lines.distance_id')
            ->where('person.active', true)
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
            ->with(['person.club', 'distance.group'])
            ->join('person', 'person.id', '=', 'protocol_lines.person_id')
            ->join('persons_payments', 'person.id', '=', 'persons_payments.person_id')
            ->join('distances', 'distances.id', '=', 'protocol_lines.distance_id')
            ->where('persons_payments.year', $cupEvent->cup->year)
            ->where('person.active', true)
            ->where('distances.event_id', $cupEvent->event_id)
            ->where('distances.group_id', $groupId)
            ->havingRaw('persons_payments.date <= ?', [$cupEvent->event->date])
            ->get()
        ;
    }

    public function getCupEventDistanceProtocolLines(int $distanceId): Collection
    {
        return ProtocolLine::where('protocol_lines.distance_id', $distanceId)
            ->with(['person.club', 'distance.group'])
            ->join('person', 'person.id', '=', 'protocol_lines.person_id')
            ->where('protocol_lines.vk', false)
            ->where('person.active', true)
            ->where('person.citizenship', Citizenship::BELARUS->value)
            ->get()
        ;
    }
}
