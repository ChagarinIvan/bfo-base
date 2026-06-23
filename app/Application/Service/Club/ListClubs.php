<?php

declare(strict_types=1);

namespace App\Application\Service\Club;

use App\Application\Dto\Club\ClubSearchDto;
use App\Domain\Shared\Criteria;
use function array_filter;
use function get_object_vars;

final readonly class ListClubs
{
    public function __construct(private ClubSearchDto $search)
    {
    }

    public function criteria(): Criteria
    {
        return new Criteria(array_filter(get_object_vars($this->search)));
    }
}
