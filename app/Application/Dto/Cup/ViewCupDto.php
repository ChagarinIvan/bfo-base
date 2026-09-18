<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

use App\Application\Dto\Auth\ImpressionDto;
use App\Application\Dto\Serialization\Groups;

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
        public bool $visible,
        #[Groups(['authenticated'])]
        public ImpressionDto $created,
        #[Groups(['authenticated'])]
        public ImpressionDto $updated,
    ) {
    }
}
