<?php

declare(strict_types=1);

namespace App\Domain\Rank\Factory;

use Carbon\Carbon;

final class FinishDateCalculator
{
    public function calculate(Carbon $startDate): Carbon
    {
        return $startDate->clone()->addYears(2);
    }
}
