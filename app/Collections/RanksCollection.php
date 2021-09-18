<?php

declare(strict_types=1);

namespace App\Collections;

use App\Models\Rank;
use Illuminate\Support\Collection;

class RanksCollection
{
    private Collection $ranks;

    public function __construct(Collection $ranks)
    {
        $this->ranks = $ranks;
    }

    public function each(\Closure $closure): void
    {
        $this->ranks->each($closure);
    }

    public function transform(\Closure $closure): void
    {
        $this->ranks = $this->ranks->transform($closure);
    }

    /**
     * @return Rank[]
     */
    public function toArray(): array
    {
        return $this->ranks->toArray();
    }

    public function getCollection(): Collection
    {
        return $this->ranks;
    }

    public function groupByPerson(): void
    {
        $this->ranks = $this->ranks->groupBy('person_id');
    }

    public function orderByFinishDateAsc(): void
    {
        $this->ranks = $this->ranks->sortBy('finish_date');
    }
}
