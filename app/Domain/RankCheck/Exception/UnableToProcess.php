<?php

declare(strict_types=1);

namespace App\Domain\RankCheck\Exception;

use DomainException;

final class UnableToProcess extends DomainException
{
    public function __construct()
    {
        parent::__construct('Rank check cannot be processed in its current state.');
    }
}
