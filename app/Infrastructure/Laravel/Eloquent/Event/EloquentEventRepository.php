<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Event;

use App\Domain\Event\Event;
use App\Domain\Event\EventRepository;
use App\Domain\Event\EventResources;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use App\Infrastructure\Laravel\Eloquent\Pagination\EloquentQueryAdapter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use function mb_strtolower;

final class EloquentEventRepository implements EventRepository
{
    public function add(Event $event): void
    {
        $event->create();
    }

    public function byId(int $id): ?Event
    {
        return Event::where('active', true)
            ->with(['competition', 'cups.cup', 'distances.group', 'flags'])
            ->find($id);
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

    /** @return Slice<Event> */
    public function paginate(Criteria $criteria, EventResources $resources = new EventResources()): Slice
    {
        $query = $this->buildQuery($criteria)->withCount('protocolLines');

        if ($resources->competitionName) {
            $query->with('competition:id,name');
        }

        return new Slice(new EloquentQueryAdapter($query));
    }

    public function oneByCriteria(Criteria $criteria): ?Event
    {
        /** @var Event|null $event */
        $event =  $this->buildQuery($criteria)->first();

        return $event;
    }

    /** @return Builder<Event> */
    private function buildQuery(Criteria $criteria): Builder
    {
        $query = Event::select('events.*')
            ->distinct()
            ->where('events.active', true)
        ;

        if ($criteria->hasParam('competitionId')) {
            $query->where('competition_id', $criteria->param('competitionId'));
        }

        if ($criteria->hasParam('groupId')) {
            $query->join('distances', 'distances.event_id', '=', 'events.id')
                ->where('distances.group_id', $criteria->param('groupId'));
        }

        if ($criteria->hasParam('competitionName')) {
            $query->join('competitions', 'competitions.id', '=', 'events.competition_id');
        }

        if ($criteria->hasParam('competitionName')) {
            $query->whereRaw('LOWER(competitions.name) LIKE ?', ['%' . mb_strtolower((string) $criteria->param('competitionName')) . '%']);
        }

        if ($criteria->hasParam('year')) {
            $query->where('events.date', 'LIKE', "{$criteria->param('year')}-%");
        }

        if ($criteria->hasParam('date')) {
            $query->whereDate('events.date', $criteria->param('date'));
        }

        if ($criteria->hasParam('flagId')) {
            $query
                ->leftjoin('event_flags', 'events.id', '=', 'event_flags.event_id')
                ->where('event_flags.flag_id', $criteria->param('flagId'));
            ;
        }

        if ($criteria->hasParam('notRelatedToCup')) {
            $query
                ->leftjoin('cup_events', 'cup_events.event_id', '=', 'events.id')
                ->where(static function ($query) use ($criteria): void {
                    $query
                        ->whereNull('cup_events.id')
                        ->orWhere(static function ($query): void {
                            $query
                                ->where('cup_events.active', false)
                                ->whereNotNull('cup_events.id')
                            ;
                        })
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
            $query->orderBy('events.date', 'asc')->orderBy('events.id');
        }

        return $query;
    }
}
