<?php

declare(strict_types=1);

namespace App\Domain\Person;

use App\Domain\Rank\CalculatedPersonRank;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Support\Collection;

interface PersonRepository
{
    public function byId(int $id): ?Person;

    public function lockById(int $id): ?Person;

    public function add(Person $person): void;

    public function byCriteria(Criteria $criteria): Collection;

    public function update(Person $person): void;

    public function saveRankProjection(Person $person, CalculatedPersonRank $projection): void;

    public function oneByCriteria(Criteria $criteria): ?Person;

    /** @return Slice<Person> */
    public function paginate(Criteria $criteria): Slice;
}
