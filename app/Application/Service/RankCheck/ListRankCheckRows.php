<?php

declare(strict_types=1);

namespace App\Application\Service\RankCheck;

use App\Application\Dto\RankCheck\SearchRankCheckRowsDto;
use App\Domain\RankCheck\RankCheckStatus;
use App\Domain\Shared\Criteria;
use function array_filter;
use function get_object_vars;

final readonly class ListRankCheckRows
{
    public function __construct(
        public int $rankCheckId,
        private SearchRankCheckRowsDto $search,
    ) {
    }

    public function criteria(): Criteria
    {
        return new Criteria(array_filter([
            'rankCheckId' => $this->rankCheckId,
            'status' => RankCheckStatus::Ready->value,
            ...get_object_vars($this->search),
        ], static fn (mixed $value): bool => $value !== null && $value !== ''));
    }
}
