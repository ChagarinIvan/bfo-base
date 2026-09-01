<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Dto\Person\LegacySearchPersonDto;
use App\Domain\Shared\Criteria;
use function array_filter;
use function get_object_vars;

final readonly class ListLegacyPersons
{
    public function __construct(private LegacySearchPersonDto $search)
    {
    }

    public function criteria(): Criteria
    {
        return new Criteria(array_filter(get_object_vars($this->search)));
    }
}
