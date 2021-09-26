<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cup;
use Illuminate\Support\Collection;

class CupsService
{
    public function getYearCups(int $year): Collection
    {
        return Cup::where('year', $year)->get();
    }
}
