<?php

declare(strict_types=1);

namespace App\Application\Dto\Group;

use App\Application\Dto\Auth\ImpressionDto;
use App\Application\Dto\Serialization\Groups;

final readonly class ViewGroupDto
{
    public function __construct(
        public string $id,
        public string $name,
        public int $distancesCount,
        #[Groups(['authenticated'])]
        public ImpressionDto $created,
        #[Groups(['authenticated'])]
        public ImpressionDto $updated,
    ) {
    }
}
