<?php

declare(strict_types=1);

namespace App\Domain\Shared\Pagination;

use ArrayIterator;
use Countable;
use InvalidArgumentException;
use Iterator;
use IteratorAggregate;
use JsonSerializable;
use Traversable;
use function array_keys;
use function array_map;
use function array_slice;
use function array_values;
use function count;
use function iterator_to_array;

/** @template T */
final class Slice implements JsonSerializable, Countable, IteratorAggregate
{
    private int $currentPage = 1;

    private int $perPage = 20;

    /** @var list<T>|null */
    private ?array $items = null;

    private ?bool $hasNext = null;

    /** @param SliceAdapter<T> $adapter */
    public function __construct(private readonly SliceAdapter $adapter)
    {
    }

    public function setCurrentPage(int $currentPage): self
    {
        if ($currentPage < 1) {
            throw new InvalidArgumentException('The page cannot be less than 1.');
        }

        $this->currentPage = $currentPage;
        $this->reset();

        return $this;
    }

    public function currentPage(): int
    {
        return $this->currentPage;
    }

    public function setPerPage(int $perPage): self
    {
        if ($perPage < 1) {
            throw new InvalidArgumentException('The perPage cannot be less than 1.');
        }

        $this->perPage = $perPage;
        $this->reset();

        return $this;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    /** @return list<T> */
    public function items(): array
    {
        if ($this->items === null) {
            $read = $this->adapter->getSlice(
                ($this->currentPage - 1) * $this->perPage,
                $this->perPage + 1,
            );
            $items = array_values($read instanceof Traversable
                ? iterator_to_array($read, preserve_keys: false)
                : $read);
            $this->hasNext = count($items) > $this->perPage;
            $this->items = array_values(array_slice($items, 0, $this->perPage));
        }

        return $this->items;
    }

    public function hasNext(): bool
    {
        $this->items();

        return $this->hasNext ?? false;
    }

    /** @return array<string, int|bool> */
    public function paginationHeaders(): array
    {
        return [
            'X-Pagination-Current-Page' => $this->currentPage,
            'X-Pagination-Per-Page' => $this->perPage,
            'X-Pagination-Has-Next' => $this->hasNext(),
        ];
    }

    /**
     * @template TTransformed
     * @param callable(T, array-key): TTransformed $transformer
     * @return Slice<TTransformed>
     */
    public function map(callable $transformer): self
    {
        $items = $this->items();
        $mappedItems = $items
                |> array_keys(...)
                |> (fn($x) => array_map($transformer, $items, $x,))
                |> array_values(...);
        $slice = new self(new ArraySliceAdapter($mappedItems));
        $slice->perPage = $this->perPage;
        $slice->currentPage = $this->currentPage;
        $slice->hasNext = $this->hasNext();
        $slice->items = $mappedItems;

        return $slice;
    }

    public function count(): int
    {
        return count($this->items());
    }

    /** @return Iterator<int, T> */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->items());
    }

    /** @return list<T> */
    public function jsonSerialize(): array
    {
        return $this->items();
    }

    private function reset(): void
    {
        $this->items = null;
        $this->hasNext = null;
    }
}
