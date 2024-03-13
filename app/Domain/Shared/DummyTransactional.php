<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use Closure;

final class DummyTransactional implements TransactionManager
{
    public function run(Closure $fn): mixed
    {
        return $fn();
    }
}
