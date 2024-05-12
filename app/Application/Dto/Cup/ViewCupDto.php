<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

use App\Application\Dto\Auth\ImpressionDto;
use App\Application\Dto\Cup\CupEvent\ViewCupEventDto;

final readonly class ViewCupDto
{
    public function __construct(
        public string $id,
        public string $name,
        public string $eventsCount,
        public int $year,
        public string $type,
        /** @var ViewCupGroupDto[] */
        public array $groups,
        /** @var ViewCupEventDto[] */
        public array $cupEvents,
        public string $lastEventDate,
        public bool $visible,
        public ImpressionDto $created,
        public ImpressionDto $updated,
    ) {
    }
}
