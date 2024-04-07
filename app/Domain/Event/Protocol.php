<?php

declare(strict_types=1);

namespace App\Domain\Event;

final readonly class Protocol
{
    public function __construct(
        public string $content,
        public string $extension,
    ) {
    }
}
