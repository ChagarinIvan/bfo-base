<?php

declare(strict_types=1);

namespace App\Application\Dto\CupEvent;

use App\Application\Dto\Auth\ImpressionDto;
use App\Application\Dto\Serialization\Groups;

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
        #[Groups(['authenticated'])]
        public ImpressionDto $created,
        #[Groups(['authenticated'])]
        public ImpressionDto $updated,
    ) {
    }
}
