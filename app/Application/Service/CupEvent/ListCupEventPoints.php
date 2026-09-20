<?php

declare(strict_types=1);

namespace App\Application\Service\CupEvent;

use App\Application\Dto\CupEvent\CupEventPointSearchDto;

final readonly class ListCupEventPoints
{
    public function __construct(
        private string $cupEventId,
        private CupEventPointSearchDto $search,
    ) {
    }

    public function cupEventId(): int
    {
        return (int) $this->cupEventId;
    }

    public function search(): CupEventPointSearchDto
    {
        return $this->search;
    }
}
