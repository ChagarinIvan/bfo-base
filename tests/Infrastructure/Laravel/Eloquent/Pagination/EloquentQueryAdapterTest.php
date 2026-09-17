<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Laravel\Eloquent\Pagination;

use App\Domain\Person\Person;
use App\Domain\Shared\Pagination\Slice;
use App\Infrastructure\Laravel\Eloquent\Pagination\EloquentQueryAdapter;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function array_filter;
use function str_contains;
use function strtolower;

final class EloquentQueryAdapterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_fetches_a_probe_row_without_counting_total_results(): void
    {
        Person::factory()->count(41)->create();
        $queries = [];
        DB::listen(static function (QueryExecuted $query) use (&$queries): void {
            $queries[] = $query;
        });

        $slice = new Slice(new EloquentQueryAdapter(
            Person::query()->orderBy('id'),
        ));
        $slice->setPerPage(20);
        $slice->setCurrentPage(2);

        $this->assertCount(20, $slice->items());
        $this->assertTrue($slice->hasNext());
        $this->assertCount(1, $queries);
        $this->assertContains(20, $queries[0]->bindings);
        $this->assertContains(21, $queries[0]->bindings);
        $this->assertCount(0, array_filter(
            $queries,
            static fn (QueryExecuted $query): bool => str_contains(strtolower($query->sql), 'count('),
        ));
    }
}
