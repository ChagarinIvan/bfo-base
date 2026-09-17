<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Pagination;

use App\Domain\Shared\Pagination\SliceAdapter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 * @implements SliceAdapter<TModel>
 */
final readonly class EloquentQueryAdapter implements SliceAdapter
{
    /** @param Builder<TModel> $query */
    public function __construct(private Builder $query)
    {
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
