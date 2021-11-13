<?php

namespace App\Services;

use App\Models\Cup;
use Illuminate\Support\Collection;

class CupsService
{
    public function getYearCups(int $year): Collection
    {
        return Cup::where('year', $year)->get();
    }

    public function deleteCup(Cup $cup): void
    {
        $cup->delete();
    }

    public function getCup(int $cupId): Cup
    {
        $cup = Cup::find($cupId);
        if ($cup) {
            return $cup;
        }
        throw new \RuntimeException('Wrong cup id.');
    }
}
