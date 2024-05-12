<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

use App\Application\Dto\Cup\CupEvent\ViewCupEventDto;

final readonly class ViewCalculatedCupEventDto
{
    public function __construct(
        public string $cupName,
        public string $cupYear,
        /** @var ViewCupGroupDto[] */
        public array $cupGroups,
        public ViewCupEventDto $cupEvent,
        /** ViewCupEventPointDto[] */
        public array $points,
    ) {
    }
}
