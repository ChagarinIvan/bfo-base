<?php

namespace App\Filters;

use Illuminate\Support\Carbon;

class RanksFilter
{
    public ?int $personId = null;
    public ?int $eventId = null;
    public ?string $rank = null;
    public ?Carbon $startDate = null;
    public ?Carbon $finishDate = null;
    public ?array $with = null;
}
