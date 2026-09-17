<?php

declare(strict_types=1);

namespace App\Domain\RankCheck\Exception;

use RuntimeException;

final class UnableToCreate extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Unable to create rank check.');
    }
}
