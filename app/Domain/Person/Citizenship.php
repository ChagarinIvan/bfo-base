<?php

declare(strict_types=1);

namespace App\Domain\Person;

enum Citizenship: string
{
    case BELARUS = 'belarus';
    case OTHER = 'other';
}
