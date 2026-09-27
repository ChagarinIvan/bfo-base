<?php

declare(strict_types=1);

namespace App\Application\Dto\RankCheck;

use App\Application\Dto\Auth\ImpressionDto;
use App\Application\Dto\Serialization\Groups;

final readonly class ViewRankCheckDto
{
    public function __construct(
        public string $id,
        public string $status,
        #[Groups(['authenticated'])]
        public ImpressionDto $created,
        #[Groups(['authenticated'])]
        public ImpressionDto $updated,
        public ?string $error = null,
    ) {
    }
}
