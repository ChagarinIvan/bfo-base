<?php

declare(strict_types=1);

namespace App\Domain\Person;

enum RankChangeType: string
{
    case Completion = 'completion';
    case Extension = 'extension';
    case Promotion = 'promotion';
    case Downgrade = 'downgrade';
}
