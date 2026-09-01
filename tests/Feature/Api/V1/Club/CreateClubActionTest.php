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

final class CreateClubActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_requires_authentication(): void
    {
        $this->postJson('/api/v1/clubs', ['name' => 'New Club'])->assertUnauthorized();
    }

    #[Test]
    public function it_creates_a_trimmed_club(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->postJson('/api/v1/clubs', ['name' => '  Новы клуб  '])
            ->assertCreated()
            ->assertJsonPath('name', 'Новы клуб')
            ->assertJsonStructure(['id', 'name', 'personsCount', 'created', 'updated']);
    }

    #[Test]
    public function it_returns_conflict_field_error_for_duplicate_name(): void
    {
        Sanctum::actingAs($this->createUser());
        Club::factory()->createOne(['name' => 'Новы клуб', 'normalize_name' => 'новы клуб']);

        $this->postJson('/api/v1/clubs', ['name' => 'Новы клуб'])
            ->assertStatus(409)
            ->assertJsonFragment(['code' => 'club_name_already_exists'])
            ->assertJsonMissingPath('errors.0.field');
    }

    #[Test]
    public function it_rejects_a_normalized_variant_of_an_existing_name(): void
    {
        Sanctum::actingAs($this->createUser());
        Club::factory()->createOne(['name' => 'БГУ', 'normalize_name' => 'бгу']);

        $this->postJson('/api/v1/clubs', ['name' => 'BSU'])
            ->assertStatus(409)
            ->assertJsonFragment(['code' => 'club_name_already_exists']);
    }

    #[Test]
    public function it_returns_validation_field_error_for_invalid_name(): void
    {
        Sanctum::actingAs($this->createUser());
        $this->postJson('/api/v1/clubs', [])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'name']);
    }

    #[Test]
    public function it_rejects_a_non_string_name_with_validation_error(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->postJson('/api/v1/clubs', ['name' => ['not', 'a', 'string']])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'name']);
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
    }
}
