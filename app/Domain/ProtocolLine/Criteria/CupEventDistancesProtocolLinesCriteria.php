<?php

declare(strict_types=1);

namespace App\Domain\ProtocolLine\Criteria;

use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Shared\Criteria;
use App\Models\Year;
use Illuminate\Support\Collection;

final readonly class CupEventDistancesProtocolLinesCriteria
{
    public static function create(Collection $distances, CupEvent $cupEvent, Year $paymentYear = null)
    {
        return new Criteria([
            'distances' => $distances->pluck('id')->unique(),
            'eventDate' => $cupEvent->event->date,
            'paymentYear' =>$paymentYear,
        ]);
    }
}
