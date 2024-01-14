<?php

declare(strict_types=1);

namespace App\Application\Dto\Rank;

use App\Application\Dto\Auth\ImpressionDto;

final readonly class ViewRankDto
{
    public function __construct(
        public string $id,
        public string $personId,
        public string $type,
        public ?string $eventId,
        public string $completedAt,
        public ?string $startedAt,
        public ?string $finishedAt,
        public ImpressionDto $created,
        public ImpressionDto $updated,
    ) {
    }
}
