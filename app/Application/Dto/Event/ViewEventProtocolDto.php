<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

final readonly class ViewEventProtocolDto
{
    public function __construct(
        public string $name,
        public string $content,
        public string $extension,
    ) {
    }
}
