<?php

declare(strict_types=1);

namespace App\Domain\Person;

enum RankCalculationReason: string
{
    case Manual = 'manual';
    case Protocol = 'protocol';
    case Daily = 'daily';
    case Refill = 'refill';
}
