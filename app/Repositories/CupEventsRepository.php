<?php

namespace App\Repositories;

use App\Models\Cup;
use App\Models\CupEvent;
use Illuminate\Support\Collection;

class CupEventsRepository
{
    public function getCupEvent(int $id): ?CupEvent
    {
        return CupEvent::find($id);
    }

    public function getCupEvents(Cup $cup): Collection
    {
        return $cup->events()->with('event')->get();
    }
}
