<?php

declare(strict_types=1);

namespace App\Domain\Shared\Pagination;

use Closure;
use Traversable;
use function array_keys;
use function array_map;
use function array_values;
use function iterator_to_array;

/**
 * @template T
 * @template TTransformed
 * @implements SliceAdapter<TTransformed>
 */
final readonly class TransformingSliceAdapter implements SliceAdapter
{
    /** @var Closure(T, array-key): TTransformed */
    private Closure $transformer;
    /**
     * @param SliceAdapter<T> $adapter
     * @param callable(T, array-key): TTransformed $transformer
     */
    public function __construct(
        private SliceAdapter $adapter,
        callable $transformer,
    ) {
        $this->transformer = Closure::fromCallable($transformer);
    }

    /** @return iterable<int, TTransformed> */
    public function getSlice(int $offset, int $length): iterable
    {
        $read = $this->adapter->getSlice($offset, $length);
        $items = array_values($read instanceof Traversable
            ? iterator_to_array($read, preserve_keys: false)
            : $read);

        return array_values(array_map(
            $this->transformer,
            $items,
            array_keys($items),
        ));
    }
}
