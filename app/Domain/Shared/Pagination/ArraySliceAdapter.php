<?php

declare(strict_types=1);

namespace App\Domain\Shared\Pagination;

use function array_slice;

/** @template T @implements SliceAdapter<T> */
final readonly class ArraySliceAdapter implements SliceAdapter
{
    /** @param list<T> $items */
    public function __construct(private array $items)
    {
    }

    /** @return iterable<int, T> */
    public function getSlice(int $offset, int $length): iterable
    {
        return array_slice($this->items, $offset, $length);
    }
}
