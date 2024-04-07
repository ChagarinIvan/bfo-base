<?php

declare(strict_types=1);

namespace App\Infrastracture\Laravel\Eloquent\Event;

use App\Domain\Event\Event;
use App\Domain\Event\EventRepository;
use App\Domain\Shared\Criteria;
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
        $query = Event::where('active', true)->orderBy('date', 'asc');

        if ($criteria->hasParam('year')) {
            $query->where('date', 'LIKE', "%{$criteria->param('year')}%");
        }

        if ($criteria->hasParam('competitionId')) {
            $query->where('competition_id', $criteria->param('competitionId'));
        }

        return $query->get();
    }
}
