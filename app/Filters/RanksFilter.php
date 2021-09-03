<?php

namespace App\Filters;

use Illuminate\Support\Carbon;

class RanksFilter
{
    public ?int $personId = null;
    public ?int $eventId = null;
    public ?string $rank = null;
    public ?Carbon $date = null;
    public ?array $with = null;
    public bool $isOrderDescByFinishDateAnd = false;
    public bool $isOrderByFinish = false;
}
