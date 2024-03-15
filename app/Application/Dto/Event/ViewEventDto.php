<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

use App\Application\Dto\Auth\ImpressionDto;
use App\Models\CupEvent;
use App\Models\Distance;
use App\Models\Flag;

final readonly class ViewEventDto
{
    public function __construct(
        public string $id,
        public string $competitionId,
        public string $name,
        public string $description,
        public string $date,

        // TODO remove laravel trush
        public int $protocolLinesCount,
        public ?Distance $firstDistance,
        /** @var CupEvent[] $cups */
        public array $cups,
        /** @var Flag[] $flags */
        public array $flags,
        public ImpressionDto $created,
        public ImpressionDto $updated,
    ) {
    }
}