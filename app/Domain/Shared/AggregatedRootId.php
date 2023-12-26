<?php

declare(strict_types=1);

namespace App\Domain\Shared;

abstract readonly class AggregatedRootId extends Uuid
{
    public function equal(self $other): bool
    {
        return $this->toString() === $other->toString();
    }
}
