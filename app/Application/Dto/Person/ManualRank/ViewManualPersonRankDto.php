<?php

declare(strict_types=1);

namespace App\Application\Dto\Person\ManualRank;

use App\Application\Dto\Auth\ImpressionDto;

final readonly class ViewManualPersonRankDto
{
    public function __construct(
        public string $id,
        public string $personId,
        public int $rankId,
        public string $achievedOn,
        public string $activatedOn,
        public ImpressionDto $created,
        public ImpressionDto $updated
    )
    {
    }
}
