<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Support\Collection;

interface EventRepository
{
    public function add(Event $event): void;

    public function lockById(int $id): ?Event;

    public function update(Event $event): void;

    public function byCriteria(Criteria $criteria): Collection;

    /** @return Slice<Event> */
    public function paginate(Criteria $criteria): Slice;

    public function oneByCriteria(Criteria $criteria): ?Event;

    public function byId(int $id): ?Event;
}
