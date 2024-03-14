<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Shared\Criteria;
use App\Models\Event;
use Illuminate\Support\Collection;

interface EventRepository
{
    public function add(Event $event): void;

    public function byCriteria(Criteria $criteria): Collection;
}
