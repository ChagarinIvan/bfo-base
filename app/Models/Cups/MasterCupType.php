<?php

namespace App\Models\Cups;

use App\Models\Cup;
use App\Services\CalculatingService;
use Illuminate\Support\Collection;

class MasterCupType implements CupTypeInterface
{
    public function getId(): string
    {
        return CupType::MASTER;
    }

    public function getName(): string
    {
        return 'Master';
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
