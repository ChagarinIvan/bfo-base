<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\PersonPrompt;

use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use App\Infrastructure\Laravel\Eloquent\Pagination\EloquentQueryAdapter;
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
        return PersonPrompt::query()->where('active', true)->find($id);
    }

    public function lockById(int $id): ?PersonPrompt
    {
        return PersonPrompt::query()->where('active', true)->lockForUpdate()->find($id);
    }

    public function byCriteria(Criteria $criteria): Collection
    {
        return $this->buildQuery($criteria)->get();
    }

    /** @return Slice<PersonPrompt> */
    public function paginate(Criteria $criteria): Slice
    {
        return new Slice(new EloquentQueryAdapter($this->buildQuery($criteria)));
    }

    public function update(PersonPrompt $prompt): void
    {
        $prompt->save();
    }

    /** @return Builder<PersonPrompt> */
    private function buildQuery(Criteria $criteria): Builder
    {
        $query = PersonPrompt::select('persons_prompt.*')
            ->join('person', 'person.id', '=', 'persons_prompt.person_id')
            ->where('persons_prompt.active', true)
            ->where('person.active', $criteria->paramOrDefault('activePerson', true));

        if ($criteria->hasParam('personId')) {
            $query->where('persons_prompt.person_id', $criteria->param('personId'));
        }

        if ($criteria->hasParam('prompts')) {
            $query->whereIn('persons_prompt.prompt', $criteria->param('prompts'));
        }

        return $query->orderBy('persons_prompt.id', 'desc');
    }
}
