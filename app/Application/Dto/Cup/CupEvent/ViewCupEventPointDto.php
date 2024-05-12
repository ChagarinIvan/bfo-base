<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup\CupEvent;

final readonly class ViewCupEventPointDto
{
    public function __construct(
        public string $cupEventId,
        public string $points,
        public string $personId,
        public string $personName,
        public int $personYear,
        public ?string $personClubId,
        public string $time,
    ) {
    }
}
