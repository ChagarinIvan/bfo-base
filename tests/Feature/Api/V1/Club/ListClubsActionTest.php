<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Club;

use App\Domain\Club\Club;
use App\Domain\Person\Person;
use App\Infrastructure\Sanctum\SanctumUser;
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

final class ListClubsActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_hides_audit_fields_for_public_client(): void
    {
        $club = $this->createClub(['id' => 1, 'name' => 'Public Club']);

        $this->getJson('/api/v1/clubs')
            ->assertOk()
            ->assertJsonPath('0.id', (string) $club->getKey())
            ->assertJsonPath('0.personsCount', 0)
            ->assertJsonMissingPath('0.created')
            ->assertJsonMissingPath('0.updated')
        ;
    }

    #[Test]
    public function it_includes_audit_fields_for_authenticated_client(): void
    {
        $this->createClub(['id' => 1]);
        Sanctum::actingAs($this->createUser());

        $this->getJson('/api/v1/clubs')
            ->assertOk()
            ->assertJsonStructure([['created', 'updated']])
        ;
    }

    #[Test]
    public function it_returns_all_active_club_options_without_pagination(): void
    {
        $this->createClub(['id' => 2, 'name' => 'Same']);
        $this->createClub(['id' => 1, 'name' => 'Same']);
        $this->createClub(['id' => 3, 'name' => 'Inactive', 'active' => false]);

        $this->getJson('/api/v1/clubs/all')
            ->assertOk()
            ->assertHeaderMissing('X-Pagination-Total')
            ->assertExactJson([
                ['id' => '1', 'name' => 'Same'],
                ['id' => '2', 'name' => 'Same'],
            ])
        ;
    }

    #[Test]
    public function it_returns_only_active_clubs_and_active_person_counts(): void
    {
        $activeClub = $this->createClub(['id' => 1, 'name' => 'Active Club']);
        $this->createClub(['id' => 2, 'name' => 'Inactive Club', 'active' => false]);
        Person::factory()->createOne(['id' => 1, 'club_id' => $activeClub->id, 'active' => true]);
        Person::factory()->createOne(['id' => 2, 'club_id' => $activeClub->id, 'active' => false]);

        $this->getJson('/api/v1/clubs')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.personsCount', 1)
            ->assertJsonPath('0.id', '1')
        ;
    }

    #[Test]
    public function it_returns_pagination_metadata(): void
    {
        $this->createClub(['id' => 1, 'name' => 'Club A']);
        $this->createClub(['id' => 2, 'name' => 'Club B']);
        $this->createClub(['id' => 3, 'name' => 'Club C']);

        $this->getJson('/api/v1/clubs?perPage=2&page=2')
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
        $matchingClub = $this->createClub(['id' => 1, 'name' => 'Minsk Orienteering']);
        $this->createClub(['id' => 2, 'name' => 'Brest Orienteering']);

        $this->getJson('/api/v1/clubs?name=%20%20mInSk%20%20')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', (string) $matchingClub->getKey())
        ;
    }

    #[Test]
    public function it_rejects_a_short_non_empty_name_filter(): void
    {
        $this->getJson('/api/v1/clubs?name=ab')
            ->assertUnprocessable()
            ->assertJsonFragment([
                'code' => 'validation_error',
                'field' => 'name',
            ])
        ;
    }

    #[Test]
    public function it_orders_clubs_by_name_and_id(): void
    {
        $this->createClub(['id' => 2, 'name' => 'Same Name']);
        $this->createClub(['id' => 1, 'name' => 'Same Name']);
        $this->createClub(['id' => 3, 'name' => 'Another Name']);

        $this->getJson('/api/v1/clubs')
            ->assertOk()
            ->assertJsonPath('0.id', '3')
            ->assertJsonPath('1.id', '1')
            ->assertJsonPath('2.id', '2')
        ;
    }

    #[Test]
    public function it_loads_clubs_without_an_n_plus_one_query(): void
    {
        $this->createClub(['id' => 1, 'name' => 'Club A']);
        $this->createClub(['id' => 2, 'name' => 'Club B']);
        $this->createClub(['id' => 3, 'name' => 'Club C']);
        $queries = [];

        DB::listen(static function (QueryExecuted $query) use (&$queries): void {
            if (str_contains(strtolower($query->sql), 'from `club`')) {
                $queries[] = strtolower($query->sql);
            }
        });

        $this->getJson('/api/v1/clubs')->assertOk();

        $this->assertCount(2, $queries);
        $this->assertCount(1, array_filter(
            $queries,
            static fn (string $sql): bool => str_contains($sql, 'limit'),
        ));
    }

    /** @param array<string, mixed> $attributes */
    private function createClub(array $attributes = []): Club
    {
        /** @var Club $club */
        $club = Club::factory()->createOne($attributes);

        return $club;
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
    }
}
