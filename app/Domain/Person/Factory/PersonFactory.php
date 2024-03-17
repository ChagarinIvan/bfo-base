<?php

declare(strict_types=1);

namespace App\Domain\Person\Factory;

use App\Domain\Person\Exception\PersonInfoAlreadyExist;
use App\Domain\Person\Person;

interface PersonFactory
{
    /** @throws PersonInfoAlreadyExist */
    public function create(PersonInput $input): Person;
}
