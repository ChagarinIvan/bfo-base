<?php

declare(strict_types=1);

namespace App\Domain\RankCheck\Factory;

use App\Domain\RankCheck\RankCheckPersonSnapshot;
use App\Domain\RankCheck\RankListItem;

final readonly class RankCheckRowInput
{
    public function __construct(
        public int $rankCheckId,
        public int $position,
        public RankListItem $line,
        public ?RankCheckPersonSnapshot $person,
    ) {
    }
}
