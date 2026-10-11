<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Dto\Person\SearchPersonDto;
use App\Domain\Shared\Criteria;

final readonly class ExportPersonRanks
{
    public function __construct(private SearchPersonDto $search)
    {
    }

    public function criteria(): Criteria
    {
        return new ListPersons($this->search)->criteria();
    }
}
