<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cup;
use App\Models\CupEvent;
use Illuminate\Support\Collection;

class CupEventsService
{
    public function getCupEvents(Cup $cup): Collection
    {
        return $cup->events()->with('event')->get();
    }

    public function getCupEventPersonsCount(CupEvent $cupEvent): int
    {
        $cupType = $cupEvent->cup->getCupType();
        return $cupType->getCupEventParticipatesCount($cupEvent);
    }

    public function getCupEvent(int $cupEventId): ?CupEvent
    {
        return CupEvent::find($cupEventId);
    }

    public function storeCupEvent(CupEvent $cupEvent): void
    {
        $cupEvent->save();
    }
}
