<?php

namespace App\Repositories;

use App\Models\Event;
use Illuminate\Support\Collection;

class EventsRepository
{
    public function getYearEvents(int $year): Collection
    {
        return Event::where('date', 'LIKE', "%{$year}%")
            ->orderBy('date')
            ->get();
    }
}
