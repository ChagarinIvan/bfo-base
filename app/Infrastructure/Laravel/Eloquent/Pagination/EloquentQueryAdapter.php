<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Pagination;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * @template TModel of Model
 * @implements AdapterInterface<TModel>
 */
final readonly class EloquentQueryAdapter implements AdapterInterface
{
    /** @param Builder<TModel> $query */
    public function __construct(private Builder $query)
    {
    }

    public function getNbResults(): int
    {
        return (clone $this->query)->count();
    }

    /** @return iterable<int, TModel> */
    public function getSlice(int $offset, int $length): iterable
    {
        return $this->query
            ->clone()
            ->offset($offset)
            ->limit($length)
            ->get()
            ->all()
        ;
    }
}
