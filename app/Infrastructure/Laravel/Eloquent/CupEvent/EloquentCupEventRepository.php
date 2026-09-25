<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\CupEvent;

use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Cup\CupEvent\CupEventResources;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use App\Infrastructure\Laravel\Eloquent\Pagination\EloquentQueryAdapter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use function array_key_exists;
use function mb_strtolower;

final class EloquentCupEventRepository implements CupEventRepository
{
    public function add(CupEvent $cupEvent): void
    {
        $cupEvent->create();
    }

    public function byId(int $id): ?CupEvent
    {
        return CupEvent::query()
            ->select('cup_events.*')
            ->join('cups', 'cups.id', '=', 'cup_events.cup_id')
            ->where('cup_events.active', true)
            ->where('cups.active', true)
            ->where('cup_events.id', $id)
            ->with('event.competition')
            ->first()
        ;
    }

    public function lockById(int $id): ?CupEvent
    {
        return CupEvent::query()
            ->select('cup_events.*')
            ->join('cups', 'cups.id', '=', 'cup_events.cup_id')
            ->where('cup_events.active', true)
            ->where('cups.active', true)
            ->where('cup_events.id', $id)
            ->lockForUpdate()
            ->first();
    }

    public function byCriteria(
        Criteria $criteria,
        CupEventResources $resources = new CupEventResources(),
    ): Collection
    {
        $query = $this->buildQuery($criteria);
        $relations = [];

        if ($resources->withCup) {
            $relations[] = 'cup';
        }

        if ($resources->withEvent) {
            $relations[] = 'event';
        }

        if ($relations !== []) {
            $query->with($relations);
        }

        return $query->get();
    }

    /** @return Slice<CupEvent> */
    public function paginate(Criteria $criteria): Slice
    {
        return new Slice(new EloquentQueryAdapter($this->buildQuery($criteria)));
    }

    public function update(CupEvent $cupEvent): void
    {
        $cupEvent->save();
    }

    /** @return Builder<CupEvent> */
    private function buildQuery(Criteria $criteria): Builder
    {
        $query = CupEvent::query()
            ->select('cup_events.*')
            ->join('cups', 'cups.id', '=', 'cup_events.cup_id')
            ->where('cup_events.active', true)
            ->where('cups.active', true)
        ;

        if ($criteria->hasParam('cupId')) {
            $query->where('cup_events.cup_id', $criteria->param('cupId'));
        }

        if ($criteria->hasParam('eventIds')) {
            $query->whereIn('cup_events.event_id', $criteria->param('eventIds'));
        }

        if ($criteria->hasParam('date')) {
            $query->whereHas('event', static fn (Builder $event): Builder => $event->whereDate('date', $criteria->param('date')));
        }

        if ($criteria->hasParam('name')) {
            $name = '%' . mb_strtolower((string) $criteria->param('name')) . '%';
            $query->whereHas('event', static fn (Builder $event): Builder => $event
                ->whereRaw('LOWER(name) LIKE ?', [$name])
                ->orWhereHas('competition', static fn (Builder $competition): Builder => $competition->whereRaw('LOWER(name) LIKE ?', [$name])));
        }

        if (array_key_exists('event.date', $criteria->sorting())) {
            $query
                ->join('events', 'events.id', '=', 'cup_events.event_id')
                ->orderBy('events.date', $criteria->sorting()['event.date'])
            ;
        } else {
            $query->orderBy('cup_events.event_id');
        }

        return $query->orderBy('cup_events.id');
    }
}
