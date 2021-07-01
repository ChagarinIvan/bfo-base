<?php

namespace App\Models\Cups;

use App\Models\Cup;
use App\Services\CalculatingService;
use Illuminate\Support\Collection;

class EliteCupType implements CupTypeInterface
{
    public function getId(): string
    {
        return CupType::ELITE;
    }

    public function getName(): string
    {
        return 'Elite';
    }

    public function calculate(Cup $cup, Collection $events, Collection $protocolLines): array
    {
        if ($protocolLines->isEmpty()) {
            return [];
        } else {
            return CalculatingService::calculateCup($cup, $events, $protocolLines);
        }
    }
}
