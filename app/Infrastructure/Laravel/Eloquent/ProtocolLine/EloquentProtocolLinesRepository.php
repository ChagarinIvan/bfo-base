<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\ProtocolLine;

use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\ProtocolLine\ProtocolLineResources;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use App\Infrastructure\Laravel\Eloquent\Pagination\EloquentQueryAdapter;
use App\Models\Year;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use function array_key_exists;
use function count;
use function mb_strtolower;

final readonly class EloquentProtocolLinesRepository implements ProtocolLineRepository
{
    public function byId(int $id, array $with = []): ?ProtocolLine
    {
        $protocolLineQuery = ProtocolLine::where('id', $id);

        if (count($with) > 0) {
            $protocolLineQuery->with($with);
        }
        return $protocolLineQuery->first();
    }

    public function lockById(int $id): ?ProtocolLine
    {
        /** @var ProtocolLine|null $protocolLine */
        $protocolLine = ProtocolLine::query()->lockForUpdate()->find($id);

        return $protocolLine;
    }

    public function byCriteria(Criteria $criteria): Collection
    {
        return $this->buildQuery($criteria)->get();
    }

    /** @return Slice<ProtocolLine> */
    public function paginate(
        Criteria $criteria,
        ProtocolLineResources $resources = new ProtocolLineResources(),
    ): Slice {
        $query = $this->buildQuery($criteria);

        if ($criteria->hasParam('distanceId')) {
            $query->orderBy('protocol_lines.id');
        } else {
            $query->orderByDesc('events.date')->orderByDesc('protocol_lines.id');
        }

        if ($resources->withEvent) {
            $query->with(['distance.event', 'distance.group']);
        }

        if ($resources->withCompetition) {
            $query->with('distance.event.competition');
        }

        return new Slice(new EloquentQueryAdapter($query));
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

    /** @return Builder<ProtocolLine> */
    private function buildQuery(Criteria $criteria): Builder
    {
        $query = ProtocolLine::select('protocol_lines.*');

        if (array_key_exists('completedRank', $criteria->sorting())) {
            $query->orderByRaw("
        CASE complete_rank
            WHEN 'МСМК' THEN 1
            WHEN 'МС' THEN 2
            WHEN 'КМС' THEN 3
            WHEN 'I' THEN 4
            WHEN 'II' THEN 5
            WHEN 'III' THEN 6
            WHEN 'Iю' THEN 7
            WHEN 'IIю' THEN 8
            WHEN 'IIIю' THEN 9
            ELSE 10
        END ASC
    ");
        }

        if ($criteria->hasParam('personId')) {
            $query
                ->join('person', 'person.id', '=', 'protocol_lines.person_id')
                ->where('person.active', true)
                ->where('protocol_lines.person_id', $criteria->param('personId'))
            ;
        }

        if (
            $criteria->hasOneParam(['dateFrom', 'dateTo', 'year', 'date', 'eventId', 'eventIds', 'massCompetition', 'competitionName', 'personId'])
            || array_key_exists('eventDate', $criteria->sorting())
        ) {
            $query
                ->join('distances', 'distances.id', '=', 'protocol_lines.distance_id')
                ->join('events', 'events.id', '=', 'distances.event_id')
                ->join('competitions', 'competitions.id', '=', 'events.competition_id')
            ;
        }

        if ($criteria->hasParam('competitionName')) {
            $query->whereRaw(
                'LOWER(competitions.name) LIKE ?',
                ['%' . mb_strtolower((string) $criteria->param('competitionName')) . '%'],
            );
        }

        if ($criteria->hasParam('eventIds')) {
            $query
                ->whereIn('distances.event_id', $criteria->param('eventIds'))
                ->addSelect('distances.event_id')
            ;
        }

        if ($criteria->hasParam('massCompetition')) {
            $query->where('competitions.mass', $criteria->param('massCompetition'));
        }

        if (array_key_exists('eventDate', $criteria->sorting())) {
            $query->orderBy('events.date', $criteria->sorting()['eventDate']);
        }

        if ($criteria->hasParam('dateFrom')) {
            $query->where('events.date', '>', $criteria->param('dateFrom'));
        }

        if ($criteria->hasParam('completedRank')) {
            if ($criteria->param('completedRank')) {
                $query->whereNotNull('complete_rank')->where('complete_rank', '!=', '');
            } else {
                $query->whereNull('complete_rank')->orWhere('complete_rank', '');
            }
        }

        if ($criteria->hasParam('dateTo')) {
            $query->where('events.date', '<=', $criteria->param('dateTo'));
        }

        if ($criteria->hasParam('year')) {
            $year = $criteria->param('year');
            $yearValue = $year instanceof Year ? $year->value : (string) $year;
            $query->where('events.date', 'LIKE', $yearValue . '-%');
        }

        if ($criteria->hasParam('date')) {
            $query->whereDate('events.date', $criteria->param('date'));
        }

        if ($criteria->hasParam('eventId')) {
            $query->where('distances.event_id', $criteria->param('eventId'));
        }

        if ($criteria->hasParam('distanceId')) {
            $query->where('protocol_lines.distance_id', $criteria->param('distanceId'));
        }

        if ($criteria->hasParam('name')) {
            $pattern = '%' . mb_strtolower((string) $criteria->param('name')) . '%';
            $query->where(static function (Builder $query) use ($pattern): void {
                $query->whereRaw('LOWER(protocol_lines.lastname) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(protocol_lines.firstname) LIKE ?', [$pattern]);
            });
        }

        if ($criteria->hasParam('preparedLine')) {
            $query->where('protocol_lines.prepared_line', $criteria->param('preparedLine'));
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
