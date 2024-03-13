<?php

declare(strict_types=1);

namespace App\Application\Dto\Competition;

use App\Application\Dto\Auth\ImpressionDto;

final readonly class ViewCompetitionDto
{
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public string $from,
        public string $to,
        public ImpressionDto $created,
        public ImpressionDto $updated,
    ) {
    }
}
