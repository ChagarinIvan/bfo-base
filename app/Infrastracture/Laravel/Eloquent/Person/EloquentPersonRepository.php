<?php

declare(strict_types=1);

namespace App\Infrastracture\Laravel\Eloquent\Person;

use App\Domain\Person\PersonRepository;
use App\Models\Person;

final class EloquentPersonRepository implements PersonRepository
{
    public function byId(int $id): ?Person
    {
        return Person::find($id);
    }
}
