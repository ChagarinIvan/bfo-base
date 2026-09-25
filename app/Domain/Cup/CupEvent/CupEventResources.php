<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupEvent;

final readonly class CupEventResources
{
    public function __construct(
        public bool $withCup = false,
        public bool $withEvent = false,
    ) {
    }
}
