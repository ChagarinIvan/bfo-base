<?php

namespace App\Filters;

use Carbon\Carbon;

class RanksFilter
{
    public ?int $personId = null;
    public ?string $rank = null;
    public ?Carbon $startDateLess = null;
    public ?Carbon $startDateMore = null;
    public ?Carbon $finishDateMore = null;
    public ?Carbon $finishDateLess = null;
    public ?array $with = null;
    public bool $isOrderDescByFinishDate = false;
    public bool $isOrderByFinish = false;
    public bool $haveNoNextRank = false;
}
