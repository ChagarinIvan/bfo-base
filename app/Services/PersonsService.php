<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PersonsRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PersonsService
{
    private PersonsRepository $personsRepository;

    public function __construct(PersonsRepository $personsRepository)
    {
        $this->personsRepository = $personsRepository;
    }

    public function getMostParticipantPersonPaginator(string $search): LengthAwarePaginator
    {
        $query = $this->personsRepository->getPersonsOrderedByProtocolLinesCountQuery();

        if (strlen($search) > 0) {
            $query->where('person.firstname', 'LIKE', '%'.$search.'%')
                ->orWhere('person.lastname', 'LIKE', '%'.$search.'%');
        }
        return $query->paginate(12);
    }

    public function allPersons(): Collection
    {
        return $this->personsRepository->getAll();
    }
}
