<?php

declare(strict_types=1);

namespace App\Domain\Cup;

interface CupCacheInvalidator
{
    public function invalidate(int $cupId): void;
}
