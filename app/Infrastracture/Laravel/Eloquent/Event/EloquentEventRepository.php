<?php

declare(strict_types=1);

namespace App\Infrastracture\Laravel\Eloquent\Event;

use App\Domain\Competition\CompetitionRepository;
use App\Domain\Event\EventRepository;
use App\Domain\Shared\Criteria;
use App\Models\Competition;
use App\Models\Event;
use Illuminate\Support\Collection;

final class EloquentEventRepository implements EventRepository
{
    public function byCriteria(Criteria $criteria): Collection
    {
        $query = Event::orderBy('date', 'asc');

        if ($criteria->hasParam('competitionId')) {
            $query->where('competition_id', $criteria->param('competitionId'));
        }

        return $query->get();
    }
}
