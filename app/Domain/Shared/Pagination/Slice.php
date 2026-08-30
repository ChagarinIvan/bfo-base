<?php

declare(strict_types=1);

namespace App\Domain\Shared\Pagination;

use ArrayIterator;
use Countable;
use InvalidArgumentException;
use Iterator;
use IteratorAggregate;
use JsonSerializable;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Adapter\TransformingAdapter;
use Pagerfanta\Pagerfanta;
use Traversable;
use function array_values;
use function count;
use function iterator_to_array;

/** @template T */
final readonly class Slice implements JsonSerializable, Countable, IteratorAggregate
{
    /** @var Pagerfanta<T> */
    private Pagerfanta $pager;

    /** @param AdapterInterface<T> $adapter */
    public function __construct(AdapterInterface $adapter)
    {
        $this->pager = new Pagerfanta($adapter);
        $this->pager->setAllowOutOfRangePages(true);
        $this->pager->setMaxPerPage(20);
    }

    public function setCurrentPage(int $currentPage): self
    {
        if ($currentPage < 1) {
            throw new InvalidArgumentException('The page cannot be less than 1.');
        }

        $this->pager->setCurrentPage($currentPage);

        return $this;
    }

    public function currentPage(): int
    {
        return $this->pager->getCurrentPage();
    }

    public function setPerPage(int $perPage): self
    {
        if ($perPage < 1) {
            throw new InvalidArgumentException('The perPage cannot be less than 1.');
        }

        $this->pager->setMaxPerPage($perPage);

        return $this;
    }

    public function perPage(): int
    {
        return $this->pager->getMaxPerPage();
    }

    /** @return list<T> */
    public function items(): array
    {
        $results = $this->pager->getCurrentPageResults();

        return array_values($results instanceof Traversable
            ? iterator_to_array($results, preserve_keys: false)
            : $results);
    }

    /** @return array<string, int> */
    public function paginationHeaders(): array
    {
        return [
            'X-Pagination-Current-Page' => $this->currentPage(),
            'X-Pagination-Per-Page' => $this->perPage(),
            'X-Pagination-Total' => $this->pager->getNbResults(),
            'X-Pagination-Last-Page' => $this->pager->getNbPages(),
        ];
    }

    /**
     * @template TTransformed
     * @param callable(T, array-key): TTransformed $transformer
     * @return Slice<TTransformed>
     */
    public function map(callable $transformer): self
    {
        $slice = new self(new TransformingAdapter(
            $this->pager->getAdapter(),
            $transformer,
        ));
        $slice->setPerPage($this->perPage());
        $slice->setCurrentPage($this->currentPage());

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
}
