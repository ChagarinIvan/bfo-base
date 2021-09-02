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
}
