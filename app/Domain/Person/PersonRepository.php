<?php

declare(strict_types=1);

namespace App\Domain\Person;

use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

interface PersonRepository
{
    public function byId(int $id, PersonResources $resources = new PersonResources()): ?Person;

    public function lockById(int $id): ?Person;

    public function add(Person $person): void;

    public function byCriteria(Criteria $criteria): Collection;

    /** @return LazyCollection<int, int> */
    public function idsByCriteria(Criteria $criteria): LazyCollection;

    public function update(Person $person): void;

    public function oneByCriteria(Criteria $criteria): ?Person;

    /** @return Slice<Person> */
    public function paginate(Criteria $criteria): Slice;
}
