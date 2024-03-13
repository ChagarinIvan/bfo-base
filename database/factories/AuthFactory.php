<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Auth\Impression;
use App\Domain\Shared\Footprint;
use function random_int;

final class AuthFactory
{
    public static function random(): Impression
    {
        return new Impression(
            at: now(),
            by: random_int(1, 100),
        );
    }
}
