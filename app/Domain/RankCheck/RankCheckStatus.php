<?php

declare(strict_types=1);

namespace App\Domain\RankCheck;

enum RankCheckStatus: string
{
    case Parsing = 'PARSING';
    case Ready = 'READY';
    case Failed = 'FAILED';
}
