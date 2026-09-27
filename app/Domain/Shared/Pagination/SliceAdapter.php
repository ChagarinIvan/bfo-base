<?php

declare(strict_types=1);

namespace App\Domain\Shared\Pagination;

/** @template T */
interface SliceAdapter
{
    /** @return iterable<int, T> */
    public function getSlice(int $offset, int $length): iterable;
}
