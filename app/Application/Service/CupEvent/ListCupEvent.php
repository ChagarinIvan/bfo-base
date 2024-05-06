<?php

declare(strict_types=1);

namespace App\Application\Service\CupEvent;

use App\Application\Dto\Competition\CompetitionSearchDto;
use App\Application\Dto\Cup\CupSearchDto;
use App\Application\Dto\CupEvent\CupEventSearchDto;
use App\Domain\Shared\Criteria;
use function array_filter;
use function get_object_vars;

final readonly class ListCupEvent
{
    public function __construct(private CupEventSearchDto $search)
    {
    }

    public function criteria(): Criteria
    {
        return new Criteria(array_filter(get_object_vars($this->search)));
    }
}
