<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Collection;
use JetBrains\PhpStorm\ArrayShape;

class QueryResult
{
    public function __construct(
        public Collection $entities,
        private readonly int $count
    ) {}

    #[ArrayShape(['rows' => "array", 'count' => "int"])]
    public function toArray(): array
    {
        return [
            'rows' => $this->entities->toArray(),
            'count' => $this->count,
        ];
    }
}
