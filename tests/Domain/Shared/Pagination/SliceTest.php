<?php

declare(strict_types=1);

namespace Tests\Domain\Shared\Pagination;

use App\Domain\Shared\Pagination\Slice;
use App\Domain\Shared\Pagination\SliceAdapter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function array_slice;
use function range;

final class SliceTest extends TestCase
{
    #[Test]
    public function it_reads_per_page_plus_one_without_count_and_trims_probe_row(): void
    {
        $adapter = new SpySliceAdapter(range(1, 21));
        $slice = new Slice($adapter);
        $slice->setPerPage(20);

        $this->assertSame(range(1, 20), $slice->items());
        $this->assertTrue($slice->hasNext());
        $this->assertSame(['offset' => 0, 'length' => 21], $adapter->read);
        $this->assertSame(0, $adapter->countCalls);
        $this->assertTrue($slice->paginationHeaders()['X-Pagination-Has-Next']);
    }

    #[Test]
    public function it_reports_no_next_page_for_empty_and_exact_pages(): void
    {
        foreach ([[], range(1, 20)] as $items) {
            $slice = new Slice(new SpySliceAdapter($items));
            $slice->setPerPage(20);

            $this->assertSame($items, $slice->items());
            $this->assertFalse($slice->hasNext());
        }
    }

    #[Test]
    public function it_reads_the_requested_offset_for_an_out_of_range_page(): void
    {
        $adapter = new SpySliceAdapter(range(1, 20));
        $slice = new Slice($adapter);
        $slice->setPerPage(20);
        $slice->setCurrentPage(2);

        $this->assertSame([], $slice->items());
        $this->assertFalse($slice->hasNext());
        $this->assertSame(['offset' => 20, 'length' => 21], $adapter->read);
    }
}

/** @implements SliceAdapter<int> */
final class SpySliceAdapter implements SliceAdapter
{
    /** @var array{offset: int, length: int}|null */
    public ?array $read = null;

    public int $countCalls = 0;

    /** @param list<int> $items */
    public function __construct(private readonly array $items)
    {
    }

    /** @return list<int> */
    public function getSlice(int $offset, int $length): iterable
    {
        $this->read = ['offset' => $offset, 'length' => $length];

        return array_slice($this->items, $offset, $length);
    }
}
