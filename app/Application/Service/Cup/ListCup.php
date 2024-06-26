<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Application\Dto\Competition\CompetitionSearchDto;
use App\Application\Dto\Cup\CupSearchDto;
use App\Domain\Shared\Criteria;
use function array_filter;
use function get_object_vars;

final readonly class ListCup
{
    public function __construct(private CupSearchDto $search)
    {
    }

    public function criteria(): Criteria
    {
        return new Criteria(array_filter(get_object_vars($this->search)));
    }
}
