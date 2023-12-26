<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use Closure;

interface TransactionManager
{
    public function run(Closure $fn): mixed;
}
