<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Laravel\Eloquent\Person;

use App\Domain\Person\Person;
use App\Domain\Rank\Rank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function array_map;
use function strtolower;

final class PersonRankQueryPlanTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_indexes_for_rank_filter_and_expiry_scan(): void
    {
        $indexes = DB::select('SHOW INDEX FROM person');
        $names = array_map(static fn (object $index): string => $index->Key_name, $indexes);

        $this->assertContains('person_current_rank_active_index', $names);
        $this->assertContains('person_current_rank_finished_index', $names);
    }

    #[Test]
    public function rank_filter_uses_materialized_column_without_loading_history(): void
    {
        Person::factory()->createOne(['id' => 1, 'current_rank' => Rank::FirstRank]);

        $queries = DB::select('EXPLAIN SELECT id FROM person FORCE INDEX (person_current_rank_active_index) WHERE active = 1 AND current_rank = ?', [Rank::FirstRank->value]);

        $this->assertNotEmpty($queries);
        $this->assertStringContainsString('current_rank', strtolower($queries[0]->Extra . ' ' . $queries[0]->key));
    }
}
