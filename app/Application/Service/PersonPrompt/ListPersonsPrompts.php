<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPrompt;

use App\Application\Dto\PersonPrompt\SearchPersonPromptDto;
use App\Domain\Shared\Criteria;
use function array_filter;
use function get_object_vars;

final readonly class ListPersonsPrompts
{
    public function __construct(
        private SearchPersonPromptDto $search,
    ) {
    }

    public function criteria(): Criteria
    {
        return new Criteria(array_filter(get_object_vars($this->search)));
    }
}
