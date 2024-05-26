<?php

declare(strict_types=1);

namespace App\Infrastracture\Laravel\Eloquent\Event;

use App\Domain\Club\Club;
use App\Domain\Event\Event;
use App\Domain\Event\EventRepository;
use App\Domain\Shared\Criteria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EloquentEventRepository implements EventRepository
{
    public function add(Event $event): void
    {
        $event->create();
    }

    public function byId(int $id): ?Event
    {
        return Event::where('active', true)->find($id);
    }

    public function lockById(int $id): ?Event
    {
        return Event::where('active', true)->lockForUpdate()->find($id);
    }

    public function update(Event $event): void
    {
        $event->save();
    }

    public function byCriteria(Criteria $criteria): Collection
    {
        return $this->buildQuery($criteria)->get();
    }

    public function oneByCriteria(Criteria $criteria): ?Event
    {
        /** @var Event|null $event */
        $event =  $this->buildQuery($criteria)->first();

        return $event;
    }

    private function buildQuery(Criteria $criteria): Builder
    {
        $query = Event::select('events.*')->distinct()->where('events.active', true);

        if ($criteria->hasParam('year')) {
            $query->where('events.date', 'LIKE', "{$criteria->param('year')}-%");
        }

        if ($criteria->hasParam('competitionId')) {
            $query->where('competition_id', $criteria->param('competitionId'));
        }

        if ($criteria->hasParam('notRelatedToCup')) {
            $query
                ->leftjoin('cup_events', 'cup_events.event_id', '=', 'events.id')
                ->where(static function ($query) use ($criteria): void {
                    $query
                        ->whereNull('cup_events.id')
                        ->orWhere(static function ($query) use ($criteria): void {
                            $query
                                ->where('cup_events.active', true)
                                ->whereNot('cup_events.cup_id', $criteria->param('notRelatedToCup'))
                            ;
                        })
                    ;
                })
            ;
        }

        if ($criteria->sorting()) {
            foreach ($criteria->sorting() as $key => $order) {
                $query->orderBy($key, $order);
            }
        } else {
            $query->orderBy('date', 'asc');
        }


        $sqlWithPlaceholders = $query->toSql();
        $bindings = $query->getBindings();

        return $query;
    }
}
