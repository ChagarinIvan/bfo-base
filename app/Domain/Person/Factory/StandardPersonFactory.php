<?php

declare(strict_types=1);

namespace App\Domain\Person\Factory;

use App\Domain\Auth\Impression;
use App\Domain\Person\Person;
use App\Domain\Shared\Clock;

final readonly class StandardPersonFactory implements PersonFactory
{
    public function __construct(private Clock $clock)
    {
    }

    public function create(PersonInput $input): Person
    {
        $person = new Person;
        $person->lastname = $input->info->lastname;
        $person->firstname = $input->info->firstname;
        $person->birthday = $input->info->birthday;
        $person->club_id = $input->info->clubId;
        $person->from_base = $input->fromBase;
        $person->active = true;
        $person->created = $person->updated = new Impression($this->clock->now(), $input->userId);

        return $person;
    }
}
