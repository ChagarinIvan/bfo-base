<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class Year
 *
 * @package App\Models
 */
class Year extends Model
{
    public const YEARS = [
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
