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
        $get_object_vars = get_object_vars($this->search);
        dump($get_object_vars);
        $array_filter = array_filter($get_object_vars);
        dump($array_filter);
        return new Criteria($array_filter);
    }
}
