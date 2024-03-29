<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Dto\Event\EventSearchDto;
use App\Domain\Shared\Criteria;
use function array_filter;
use function get_object_vars;

final readonly class ListEvents
{
    public function __construct(private EventSearchDto $search)
    {
    }

    public function criteria(): Criteria
    {
        return new Criteria(array_filter(get_object_vars($this->search)));
    }
}
