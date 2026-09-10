<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

final readonly class ViewEventCupDto
{
    public function __construct(
        public string $id,
        public string $name,
        public int $year,
    ) {
    }
}
