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

    public function previous(): Year
    {
        return self::from($this->value - 1);
    }

    public function toString(): string
    {
        return (string) $this->value;
    }

    case y2025 = 2025;
    case y2024 = 2024;
    case y2023 = 2023;
    case y2022 = 2022;
    case y2021 = 2021;
    case y2020 = 2020;
    case y2019 = 2019;
    case y2018 = 2018;
    case y2017 = 2017;
    case y2016 = 2016;
    case y2015 = 2015;
    case y2014 = 2014;
    case y2013 = 2013;
    case y2012 = 2012;
    case y2011 = 2011;
    case y2010 = 2010;
}
