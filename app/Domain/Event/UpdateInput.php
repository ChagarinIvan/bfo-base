<?php

declare(strict_types=1);

namespace App\Domain\Event;

final readonly class UpdateInput
{
    public function __construct(
        public EventInfo $info,
        public ?Protocol $protocol,
    ) {
    }
}
