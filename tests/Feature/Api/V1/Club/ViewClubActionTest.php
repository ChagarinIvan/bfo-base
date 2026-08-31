<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Club;

use App\Domain\Club\Club;
use App\Domain\Person\Person;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ViewClubActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_an_active_club_with_only_active_person_count_to_a_public_client(): void
    {
        $club = $this->createClub(['id' => 1, 'name' => 'Minsk Club']);
        Person::factory()->createOne(['id' => 1, 'club_id' => $club->id, 'active' => true]);
        Person::factory()->createOne(['id' => 2, 'club_id' => $club->id, 'active' => false]);

        $this->getJson("/api/v1/clubs/{$club->id}")
            ->assertOk()
            ->assertJsonPath('id', '1')
            ->assertJsonPath('name', 'Minsk Club')
            ->assertJsonPath('personsCount', 1)
            ->assertJsonMissingPath('created')
            ->assertJsonMissingPath('updated')
        ;
    }

    #[Test]
    public function it_includes_impressions_for_an_authenticated_client(): void
    {
        $club = $this->createClub(['id' => 1]);
        Sanctum::actingAs($this->createUser());

        $this->getJson("/api/v1/clubs/{$club->id}")
            ->assertOk()
            ->assertJsonStructure(['created', 'updated'])
        ;
    }

    #[Test]
    public function it_returns_not_found_for_missing_or_inactive_clubs(): void
    {
        $inactiveClub = $this->createClub(['id' => 1, 'active' => false]);

        $this->getJson('/api/v1/clubs/999999')
            ->assertNotFound()
            ->assertJsonPath('errors.0.code', 'not_found')
        ;
        $this->getJson("/api/v1/clubs/{$inactiveClub->id}")
            ->assertNotFound()
            ->assertJsonPath('errors.0.code', 'not_found')
        ;
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
