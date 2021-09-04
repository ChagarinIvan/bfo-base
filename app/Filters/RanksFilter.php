<?php

namespace App\Filters;

use Carbon\Carbon;

class RanksFilter
{
    public ?int $personId = null;
    public ?int $eventId = null;
    public ?string $rank = null;
    public ?Carbon $startDateLess = null;
    public ?Carbon $startDateMore = null;
    public ?Carbon $finishDateMode = null;
    public ?array $with = null;
    public bool $isOrderDescByFinishDateAnd = false;
    public bool $isOrderByFinish = false;
}
