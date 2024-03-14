<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;

interface EventRepository
{
    public function byCriteria(Criteria $criteria): Collection;
}
