<?php

declare(strict_types=1);

namespace App\Application\Dto\Competition;

use App\Application\Dto\Auth\ImpressionDto;
use App\Application\Dto\Serialization\Groups;

final readonly class ViewCompetitionDto
{
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public string $from,
        public string $to,
        public int $year,
        public bool $mass,
        #[Groups(['authenticated'])]
        public ImpressionDto $created,
        #[Groups(['authenticated'])]
        public ImpressionDto $updated,
    ) {
    }
}
