<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

use App\Application\Dto\Auth\ImpressionDto;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Distance\Distance;
use App\Models\Flag;

final readonly class ViewEventProtocolDto
{
    public function __construct(
        public string $name,
        public string $content,
        public string $extension,
    ) {
    }
}
