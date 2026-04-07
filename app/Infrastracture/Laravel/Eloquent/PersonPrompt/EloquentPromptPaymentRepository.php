<?php

declare(strict_types=1);

namespace App\Infrastracture\Laravel\Eloquent\PersonPrompt;

use App\Domain\Cup\Cup;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\Shared\Criteria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EloquentPromptPaymentRepository implements PersonPromptRepository
{
    public function add(PersonPrompt $prompt): void
    {
        $prompt->create();
    }

    public function byId(int $id): ?PersonPrompt
    {
        return PersonPrompt::find($id);
    }

    public function lockById(int $id): ?PersonPrompt
    {
        return PersonPrompt::lockForUpdate()->find($id);
    }

    public function byCriteria(Criteria $criteria): Collection
    {
        return $this->buildQuery($criteria)->get();
    }

    public function update(PersonPrompt $prompt): void
    {
        $prompt->save();
    }

    public function delete(PersonPrompt $prompt): void
    {
        $prompt->delete();
    }

    private function buildQuery(Criteria $criteria): Builder
    {
        return PersonPrompt::select('persons_prompt.*')
            ->join('person', 'person.id', '=', 'persons_prompt.person_id')
            ->where('person.active', true)
            ->where('persons_prompt.person_id', $criteria->param('personId'))
            ->orderBy('persons_prompt.id', 'desc')
        ;
    }
}
