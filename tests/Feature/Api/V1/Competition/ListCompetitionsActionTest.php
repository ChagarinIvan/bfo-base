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

        $this->getJson('/api/v1/competitions?perPage=2&page=2')
            ->assertOk()
            ->assertHeader('X-Pagination-Total', '3')
            ->assertHeader('X-Pagination-Per-Page', '2')
            ->assertHeader('X-Pagination-Current-Page', '2')
            ->assertHeader('X-Pagination-Last-Page', '2')
        ;
    }

    #[Test]
    public function it_matches_a_trimmed_name_without_regard_to_case(): void
    {
        $matchingCompetition = $this->createCompetition(['name' => 'Minsk Cup']);
        $this->createCompetition(['name' => 'Brest Cup']);

        $this->getJson('/api/v1/competitions?name=%20%20mInSk%20%20')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', (string) $matchingCompetition->getKey())
        ;
    }

    #[Test]
    public function it_rejects_a_short_non_empty_name_filter(): void
    {
        $this->getJson('/api/v1/competitions?name=ab')
            ->assertUnprocessable()
            ->assertJsonFragment([
                'code' => 'validation_error',
                'field' => 'name',
            ])
        ;
    }

    #[Test]
    public function it_includes_competitions_on_both_date_range_boundaries(): void
    {
        $competition = $this->createCompetition([
            'from' => '2026-05-10',
            'to' => '2026-05-12',
        ]);

        $this->getJson('/api/v1/competitions?date=2026-05-10')
            ->assertOk()
            ->assertJsonFragment(['id' => (string) $competition->getKey()])
        ;
        $this->getJson('/api/v1/competitions?date=2026-05-12')
            ->assertOk()
            ->assertJsonFragment(['id' => (string) $competition->getKey()])
        ;
    }

    #[Test]
    public function it_combines_year_name_and_date_filters(): void
    {
        $matchingCompetition = $this->createCompetition([
            'name' => 'Forest Cup',
            'from' => '2026-06-10',
            'to' => '2026-06-12',
        ]);
        $this->createCompetition([
            'name' => 'Forest Cup',
            'from' => '2026-07-10',
            'to' => '2026-07-12',
        ]);
        $this->createCompetition([
            'name' => 'Forest Cup',
            'from' => '2025-06-10',
            'to' => '2025-06-12',
        ]);

        $this->getJson('/api/v1/competitions?year=2026&name=Cup&date=2026-06-11')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', (string) $matchingCompetition->getKey())
        ;
    }

    #[Test]
    public function it_does_not_return_inactive_competitions(): void
    {
        $activeCompetition = $this->createCompetition(['active' => true]);
        $this->createCompetition(['active' => false]);

        $this->getJson('/api/v1/competitions')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', (string) $activeCompetition->getKey())
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

    /** @param array<string, mixed> $attributes */
    private function createCompetition(array $attributes = []): Competition
    {
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne([
            'id' => ((int) Competition::query()->max('id')) + 1,
            'from' => '2026-01-01',
            'to' => '2026-01-02',
            ...$attributes,
        ]);

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
