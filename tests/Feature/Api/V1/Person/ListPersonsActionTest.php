<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Person;

use App\Domain\Club\Club;
use App\Domain\Person\Person;
use App\Domain\Rank\Rank;
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

final class ListPersonsActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_ignores_the_legacy_snake_case_club_filter(): void
    {
        $this->createPerson(['id' => 1, 'lastname' => 'Alpha']);

        $this->getJson('/api/v1/persons?club_id=1')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', '1')
        ;
    }

    #[Test]
    public function it_returns_the_general_active_person_list_when_club_id_is_absent(): void
    {
        $activeClub = $this->createClub(['id' => 1]);
        $inactiveClub = $this->createClub(['id' => 2, 'active' => false]);
        $this->createPerson(['id' => 1, 'club_id' => $activeClub->id, 'lastname' => 'Alpha']);
        $this->createPerson(['id' => 2, 'club_id' => $inactiveClub->id, 'lastname' => 'Beta']);
        $this->createPerson(['id' => 3, 'active' => false, 'lastname' => 'Gamma']);

        $this->getJson('/api/v1/persons')
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.id', '1')
            ->assertJsonPath('1.id', '2')
            ->assertJsonMissingPath('0.birthday')
            ->assertJsonMissingPath('0.citizenship')
            ->assertJsonMissingPath('0.clubId')
            ->assertJsonMissingPath('0.created')
        ;
    }

    #[Test]
    public function it_filters_persons_by_materialized_rank(): void
    {
        $this->createPerson(['id' => 1, 'current_rank' => Rank::FirstRank]);
        $this->createPerson(['id' => 2, 'current_rank' => Rank::SecondRank]);
        $this->createPerson(['id' => 3, 'current_rank' => Rank::WithoutRank]);
        DB::table('person')->where('id', 1)->update(['current_rank' => Rank::FirstRank->value]);
        DB::table('person')->where('id', 2)->update(['current_rank' => Rank::SecondRank->value]);
        DB::table('person')->where('id', 3)->update(['current_rank' => Rank::WithoutRank->value]);

        $this->getJson('/api/v1/persons?rankId=6')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', '1')
            ->assertJsonPath('0.rankId', 6)
        ;
    }

    #[Test]
    public function it_filters_persons_without_a_rank(): void
    {
        $this->createPerson(['id' => 1, 'current_rank' => Rank::FirstRank]);
        $this->createPerson(['id' => 2, 'current_rank' => Rank::WithoutRank]);
        DB::table('person')->where('id', 1)->update(['current_rank' => Rank::FirstRank->value]);
        DB::table('person')->where('id', 2)->update(['current_rank' => Rank::WithoutRank->value]);

        $this->getJson('/api/v1/persons?rankId=0')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', '2')
            ->assertJsonPath('0.rankId', 0)
        ;
    }

    #[Test]
    public function it_rejects_an_unknown_rank_id(): void
    {
        $this->getJson('/api/v1/persons?rankId=99')
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.field', 'rankId')
        ;
    }

    #[Test]
    public function it_returns_only_active_persons_of_an_active_filtered_club_in_stable_order(): void
    {
        $club = $this->createClub(['id' => 1]);
        $this->createPerson(['id' => 2, 'club_id' => $club->id, 'lastname' => 'Same', 'firstname' => 'Name']);
        $this->createPerson(['id' => 1, 'club_id' => $club->id, 'lastname' => 'Same', 'firstname' => 'Name']);
        $this->createPerson(['id' => 3, 'club_id' => $club->id, 'lastname' => 'Before', 'firstname' => 'Zed']);
        $this->createPerson(['id' => 4, 'club_id' => $club->id, 'active' => false]);
        $this->createPerson(['id' => 5, 'lastname' => 'Other']);

        $this->getJson("/api/v1/persons?clubId={$club->id}&perPage=2&page=1")
            ->assertOk()
            ->assertJsonPath('0.id', '3')
            ->assertJsonPath('1.id', '1')
            ->assertHeader('X-Pagination-Total', '3')
            ->assertHeader('X-Pagination-Per-Page', '2')
            ->assertHeader('X-Pagination-Current-Page', '1')
            ->assertHeader('X-Pagination-Last-Page', '2')
        ;
    }

    #[Test]
    public function it_returns_an_empty_list_for_a_missing_or_inactive_filtered_club(): void
    {
        $inactiveClub = $this->createClub(['id' => 1, 'active' => false]);
        $this->createPerson(['id' => 1, 'club_id' => $inactiveClub->id]);

        $this->getJson('/api/v1/persons?clubId=999999')
            ->assertOk()
            ->assertExactJson([])
        ;
        $this->getJson("/api/v1/persons?clubId={$inactiveClub->id}")
            ->assertOk()
            ->assertExactJson([])
        ;
    }

    #[Test]
    public function it_includes_compact_impressions_for_an_authenticated_client(): void
    {
        $this->createPerson(['id' => 1, 'birthday' => '2001-06-04']);
        Sanctum::actingAs($this->createUser());

        $this->getJson('/api/v1/persons')
            ->assertOk()
            ->assertJsonPath('0.birthYear', 2001)
            ->assertJsonStructure([['created', 'updated']])
        ;
    }

    #[Test]
    public function it_loads_persons_without_an_n_plus_one_query(): void
    {
        $this->createPerson(['id' => 1, 'lastname' => 'Alpha']);
        $this->createPerson(['id' => 2, 'lastname' => 'Beta']);
        $this->createPerson(['id' => 3, 'lastname' => 'Gamma']);
        $queries = [];

        DB::listen(static function (QueryExecuted $query) use (&$queries): void {
            if (str_contains(strtolower($query->sql), 'from `person`')) {
                $queries[] = strtolower($query->sql);
            }
        });

        $this->getJson('/api/v1/persons')->assertOk();

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

    /** @param array<string, mixed> $attributes */
    private function createPerson(array $attributes = []): Person
    {
        /** @var Person $person */
        $person = Person::factory()->createOne([
            'active' => true,
            ...$attributes,
        ]);

        return $person;
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
    }
}
