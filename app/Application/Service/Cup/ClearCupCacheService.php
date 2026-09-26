<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Domain\Cup\CupCacheInvalidator;

final readonly class ClearCupCacheService
{
    public function __construct(private CupCacheInvalidator $invalidator)
    {
    }

    public function execute(): void
    {
        $this->invalidator->invalidate();
    }
}
