<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Competition;

use App\Domain\Competition\Competition;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UpdateCompetitionActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_updates_an_active_competition_with_v1_camel_case_json(): void
    {
        $competition = $this->createCompetition();
        $user = $this->createUser();
        Sanctum::actingAs($user);

        $this->putJson("/api/v1/competitions/{$competition->id}", [
            'name' => 'Minsk Championship',
            'description' => 'Forest sprint',
            'from' => '2026-05-10',
            'to' => '2026-05-11',
            'mass' => false,
        ])
            ->assertOk()
            ->assertJsonPath('name', 'Minsk Championship')
            ->assertJsonPath('mass', false)
            ->assertJsonPath('updated.by', (string) $user->id)
        ;

        $this->assertDatabaseHas('competitions', [
            'id' => $competition->id,
            'name' => 'Minsk Championship',
            'active' => true,
            'updated_by' => $user->id,
        ]);
    }

    #[Test]
    public function it_requires_authentication_and_valid_shared_form_data(): void
    {
        $competition = $this->createCompetition();
        $payload = [
            'name' => 'Cup',
            'description' => 'Sprint',
            'from' => '2026-05-12',
            'to' => '2026-05-10',
            'mass' => false,
        ];

        $this->putJson("/api/v1/competitions/{$competition->id}", $payload)
            ->assertUnauthorized()
        ;
        Sanctum::actingAs($this->createUser());
        $this->putJson("/api/v1/competitions/{$competition->id}", $payload)
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.field', 'to')
        ;
    }

    #[Test]
    public function it_returns_not_found_for_missing_or_inactive_competitions(): void
    {
        $inactiveCompetition = $this->createCompetition(['active' => false]);
        Sanctum::actingAs($this->createUser());
        $payload = $this->payload();

        $this->putJson('/api/v1/competitions/999999', $payload)->assertNotFound();
        $this->putJson("/api/v1/competitions/{$inactiveCompetition->id}", $payload)
            ->assertNotFound()
        ;
    }

    /** @param array<string, mixed> $attributes */
    private function createCompetition(array $attributes = []): Competition
    {
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne([
            'from' => '2026-05-01',
            'to' => '2026-05-02',
            ...$attributes,
        ]);

        return $competition;
    }

    /** @return array<string, string|bool> */
    private function payload(): array
    {
        return [
            'name' => 'Minsk Championship',
            'description' => 'Forest sprint',
            'from' => '2026-05-10',
            'to' => '2026-05-11',
            'mass' => false,
        ];
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
    }
}
