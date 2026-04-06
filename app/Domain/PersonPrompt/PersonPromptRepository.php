<?php

declare(strict_types=1);

namespace App\Domain\PersonPrompt;

use App\Domain\Rank\Rank;
use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;

interface PersonPromptRepository
{
    public function byId(int $id): ?PersonPrompt;

    public function lockById(int $id): ?PersonPrompt;

    public function byCriteria(Criteria $criteria): Collection;

    public function add(PersonPrompt $prompt): void;

    public function update(PersonPrompt $prompt): void;

    public function delete(PersonPrompt $prompt): void;
}
