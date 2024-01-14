<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;

enum Year: int
{
    public static function actualYear(): self
    {
        return self::fromDate(Carbon::now());
    }

    public static function fromDate(Carbon $date): self
    {
        return self::from($date->year);
    }
    case y2025 = 2025;
    case y2024 = 2024;
    case y2023 = 2023;
    case y2022 = 2022;
    case y2021 = 2021;
    case y2020 = 2020;
    case y2019 = 2019;
    case y2018 = 2018;
}
