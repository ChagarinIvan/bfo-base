<?php

namespace App\Repositories;

use App\Models\CupEvent;

class CupEventsRepository
{
    public function getCupEvent(int $id): ?CupEvent
    {
        return CupEvent::find($id);
    }
}
