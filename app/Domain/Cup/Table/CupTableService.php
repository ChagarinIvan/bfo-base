<?php

declare(strict_types=1);

namespace App\Domain\Cup\Table;

use App\Domain\Cup\Cup;
use App\Domain\Cup\Group\CupGroup;
use Illuminate\Support\Collection;

interface CupTableService
{
    public function build(Cup $cup, Collection $events, CupGroup $group): CupTable;
}
