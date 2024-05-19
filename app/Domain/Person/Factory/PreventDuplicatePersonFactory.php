<?php

declare(strict_types=1);

namespace App\Domain\Person\Factory;

use App\Domain\Person\Exception\PersonInfoAlreadyExist;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRepository;
use App\Domain\Shared\Criteria;

final readonly class PreventDuplicatePersonFactory implements PersonFactory
{
    public function __construct(
        private PersonFactory $decorated,
        private PersonRepository $persons,
    ) {
    }

    public function create(PersonInput $input): Person
    {
        if ($person = $this->persons->oneByCriteria(new Criteria(['info' => $input->info]))) {
            throw PersonInfoAlreadyExist::byInfo($input->info, $person->id);
        }

        return $this->decorated->create($input);
    }
}
