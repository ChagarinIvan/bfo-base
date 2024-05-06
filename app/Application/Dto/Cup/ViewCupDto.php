<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

use App\Application\Dto\Auth\ImpressionDto;
use App\Application\Dto\CupEvent\ViewCupEventDto;

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
        public string $lastEventDate,
        public bool $visible,
        public ImpressionDto $created,
        public ImpressionDto $updated,

        // TODO replace with CupEventRepository or cupEventListService
        /** @var ViewCupEventDto[] */
        public array $cupEvents,
        /** @var array<int, int> */
        public array $cupEventsParticipatesCount,
    ) {
    }
}
