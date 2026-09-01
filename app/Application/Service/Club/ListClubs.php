<?php

declare(strict_types=1);

namespace App\Application\Service\Club;

use App\Application\Dto\Club\SearchClubDto;
use App\Domain\Shared\Criteria;
use function array_filter;
use function get_object_vars;
use function trim;

final readonly class ListClubs
{
    public function __construct(private SearchClubDto $search)
    {
    }

    public function criteria(): Criteria
    {
        $params = get_object_vars($this->search);
        if ($params['name'] !== null) {
            $params['name'] = trim($params['name']);
        }

        return new Criteria(array_filter(
            $params,
            static fn (mixed $value): bool => $value !== null && $value !== '',
        ));
    }
}
