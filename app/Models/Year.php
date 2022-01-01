<?php

namespace App\Models;

use Illuminate\Support\Carbon;

/**
 * Class Year
 */
class Year
{
    public const YEARS = [
        2022,
        2021,
        2020,
        2019,
        2018
    ];

    public static function actualYear(): int
    {
        return Carbon::now()->year;
    }
}
