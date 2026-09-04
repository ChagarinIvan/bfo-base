<?php

declare(strict_types=1);

namespace App\Domain\Group;

use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Support\Collection;

interface GroupRepository
{
    public function byId(int $id): ?Group;

    public function lockById(int $id): ?Group;

    public function oneByCriteria(Criteria $criteria): ?Group;

    public function add(Group $group): void;

    /** @return Collection<int, Group> */
    public function byCriteria(Criteria $criteria): Collection;

    /** @return Slice<Group> */
    public function paginate(Criteria $criteria): Slice;

    /** @return Collection<int, Group> */
    public function all(): Collection;

    public function update(Group $group): void;
}
