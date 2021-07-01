<?php

namespace App\Models\Cups;

use App\Models\Cup;
use App\Services\CalculatingService;
use Illuminate\Support\Collection;

class SprintCupType implements CupTypeInterface
{
    public function getId(): string
    {
        return CupType::SPRINT;
    }

    public function getName(): string
    {
        return 'Sprint';
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
