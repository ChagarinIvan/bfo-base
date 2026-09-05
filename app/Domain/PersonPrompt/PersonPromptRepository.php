<?php

declare(strict_types=1);

namespace App\Domain\PersonPrompt;

use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Support\Collection;

interface PersonPromptRepository
{
    public function byId(int $id): ?PersonPrompt;

    public function lockById(int $id): ?PersonPrompt;

    /** @return Collection<int, PersonPrompt> */
    public function byCriteria(Criteria $criteria): Collection;

    /** @return Slice<PersonPrompt> */
    public function paginate(Criteria $criteria): Slice;

    public function add(PersonPrompt $prompt): void;

    public function update(PersonPrompt $prompt): void;
}
