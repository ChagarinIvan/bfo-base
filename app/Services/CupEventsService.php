<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cup;
use App\Models\CupEvent;
use App\Repositories\CupEventsRepository;
use Illuminate\Support\Collection;

class CupEventsService
{
    private CupEventsRepository $cupEventsRepository;

    public function __construct(CupEventsRepository $cupEventsRepository)
    {
        $this->cupEventsRepository = $cupEventsRepository;
    }

    public function getCupEvents(Cup $cup): Collection
    {
        return $this->cupEventsRepository->getCupEvents($cup);
    }

    public function getCupEventPersonsCount(CupEvent $cupEvent): int
    {
        $cupType = $cupEvent->cup->getCupType();
        return $cupType->getCupEventParticipatesCount($cupEvent);
    }
}
