<?php

declare(strict_types=1);

namespace App\Application\Service\Competition;

use App\Application\Dto\Competition\SearchCompetitionDto;
use App\Domain\Shared\Criteria;
use function array_filter;
use function get_object_vars;

final readonly class ListCompetitions
{
    public function __construct(private SearchCompetitionDto $search)
    {
    }

    public function criteria(): Criteria
    {
        return new Criteria(array_filter(
            get_object_vars($this->search),
            static fn (mixed $value): bool => $value !== null,
        ));
    }
}
