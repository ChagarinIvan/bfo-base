<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Competition;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Competition\ListCompetitionsAction;
use App\Domain\Competition\Competition;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function array_filter;
use function str_contains;
use function strtolower;

/** @see ListCompetitionsAction */
final class ListCompetitionsActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_hides_audit_fields_for_public_client(): void
    {
        $competition = $this->createCompetition();

        $this->getJson('/api/v1/competitions')
            ->assertOk()
            ->assertJsonPath('0.id', (string) $competition->getKey())
            ->assertJsonMissingPath('0.created')
            ->assertJsonMissingPath('0.updated')
        ;
    }

    #[Test]
    public function it_includes_audit_fields_for_authenticated_client(): void
    {
        $this->createCompetition();
        Sanctum::actingAs($this->createUser());

        $this->getJson('/api/v1/competitions')
            ->assertOk()
            ->assertJsonStructure([['created', 'updated']])
        ;
    }

    #[Test]
    public function it_returns_paginated_collection_metadata(): void
    {
        Competition::factory()
            ->count(3)
            ->sequence(static fn (Sequence $sequence): array => [
                'id' => $sequence->index + 1,
            ])
            ->create([
                'from' => '2026-01-01',
                'to' => '2026-01-02',
            ]);

        $this->getJson('/api/v1/competitions?per_page=2&page=2')
            ->assertOk()
            ->assertHeader('X-Pagination-Total', '3')
            ->assertHeader('X-Pagination-Per-Page', '2')
            ->assertHeader('X-Pagination-Current-Page', '2')
            ->assertHeader('X-Pagination-Last-Page', '2')
        ;
    }

    #[Test]
    public function it_loads_competitions_without_an_n_plus_one_query(): void
    {
        Competition::factory()
            ->count(3)
            ->sequence(static fn (Sequence $sequence): array => [
                'id' => $sequence->index + 1,
            ])
            ->create([
                'from' => '2026-01-01',
                'to' => '2026-01-02',
            ]);
        $queries = [];

        DB::listen(static function (QueryExecuted $query) use (&$queries): void {
            if (str_contains(strtolower($query->sql), 'from `competitions`')) {
                $queries[] = strtolower($query->sql);
            }
        });

        $this->getJson('/api/v1/competitions')->assertOk();

        $this->assertCount(2, $queries);
        $this->assertCount(1, array_filter(
            $queries,
            static fn (string $sql): bool => str_contains($sql, 'limit'),
        ));
    }

    private function createCompetition(): Competition
    {
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne(['from' => '2026-01-01', 'to' => '2026-01-02']);

        return $competition;
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
    }
}
