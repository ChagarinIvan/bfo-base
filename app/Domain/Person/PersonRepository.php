<?php

declare(strict_types=1);

namespace App\Domain\Person;

use App\Models\Person;

interface PersonRepository
{
    public function byId(int $id): ?Person;
}
