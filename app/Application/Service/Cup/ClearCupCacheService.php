<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Application\Service\Cup\Exception\CupNotFound;
use App\Domain\Cup\CupCacheInvalidator;
use App\Domain\Cup\CupRepository;

final readonly class ClearCupCacheService
{
    public function __construct(
        private CupCacheInvalidator $invalidator,
        private CupRepository $cups,
    ) {
    }

    public function execute(ClearCupCache $command): void
    {
        if ($this->cups->byId($command->id()) === null) {
            throw new CupNotFound();
        }

        $this->invalidator->invalidate($command->id());
    }
}
