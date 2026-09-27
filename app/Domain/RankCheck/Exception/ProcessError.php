<?php

declare(strict_types=1);

namespace App\Domain\RankCheck\Exception;

use Exception;

final class ProcessError extends Exception
{
    public function __construct(?Exception $previous = null)
    {
        parent::__construct('Не удалось обработать список разрядов.', previous: $previous);
    }
}
