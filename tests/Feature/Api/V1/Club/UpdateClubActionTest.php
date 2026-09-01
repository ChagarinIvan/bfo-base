<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Club;

use App\Domain\Club\Club;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UpdateClubActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_requires_authentication(): void
    {
        $club = $this->createClub();
        $this->putJson("/api/v1/clubs/{$club->id}", ['name' => 'New name'])->assertUnauthorized();
    }

    #[Test]
    public function it_updates_an_active_club(): void
    {
        $user = $this->createUser();
        Sanctum::actingAs($user);
        $club = $this->createClub(['name' => 'Old name', 'normalize_name' => 'old name']);

        $this->putJson("/api/v1/clubs/{$club->id}", ['name' => 'Новы клуб'])
            ->assertOk()
            ->assertJsonPath('name', 'Новы клуб');
        $this->assertDatabaseHas('club', ['id' => $club->id, 'name' => 'Новы клуб', 'normalize_name' => 'новы клуб']);
    }

    #[Test]
    public function it_returns_not_found_for_missing_or_inactive_club(): void
    {
        Sanctum::actingAs($this->createUser());
        $inactive = $this->createClub(['active' => false]);

        $this->putJson('/api/v1/clubs/999999', ['name' => 'Новы клуб'])->assertNotFound();
        $this->putJson("/api/v1/clubs/{$inactive->id}", ['name' => 'Новы клуб'])->assertNotFound();
    }

    #[Test]
    public function it_returns_conflict_for_duplicate_name(): void
    {
        Sanctum::actingAs($this->createUser());
        $this->createClub(['name' => 'Іншы клуб', 'normalize_name' => 'іншы клуб']);
        $club = $this->createClub(['name' => 'Бягучы клуб', 'normalize_name' => 'бягучы клуб']);

        $this->putJson("/api/v1/clubs/{$club->id}", ['name' => 'Іншы клуб'])
            ->assertStatus(409)
            ->assertJsonFragment(['code' => 'club_name_already_exists'])
            ->assertJsonMissingPath('errors.0.field');
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
    }

    /** @param array<string, mixed> $attributes */
    private function createClub(array $attributes = []): Club
    {
        /** @var Club $club */
        $club = Club::factory()->createOne($attributes);

        return $club;
    }
}
