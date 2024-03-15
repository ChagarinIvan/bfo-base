<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use function crc32;
use function dechex;
use function substr;

class Color extends Facade
{
    public static function getColor(string $color): string
    {
        $code = dechex(crc32($color));
        $code = substr($code, 0, 6);
        return '#' . $code;
    }
}
