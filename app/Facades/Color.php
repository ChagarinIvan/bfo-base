<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Color extends Facade
{
    public static function getColor(string $color): string
    {
        $code = dechex(crc32($color));
        $code = substr($code, 0, 6);
        return '#'.$code;
    }
}
