<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

use App\Application\Dto\Auth\ImpressionDto;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Distance\Distance;

final readonly class LegacyViewEventDto
{
    public function __construct(
        public string $id,
        public string $competitionId,
        public string $name,
        public string $description,
        public string $date,

        // TODO remove laravel trush
        public string $competitionName,
        public int $protocolLinesCount,
        public ?Distance $firstDistance,
        /** @var CupEvent[] $cups */
        public array $cups,
        /** @var Distance[] $distances */
        public array $distances,
        public ImpressionDto $created,
        public ImpressionDto $updated,
    ) {
    }
}
