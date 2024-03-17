<?php

declare(strict_types=1);

namespace App\Application\Dto\Club;

use App\Application\Dto\Auth\ImpressionDto;

final readonly class ViewClubDto
{
    public function __construct(
        public string $id,
        public string $name,

        // TODO old trash
        public int $personsCount,
        public ImpressionDto $created,
        public ImpressionDto $updated,
    ) {
    }
}
