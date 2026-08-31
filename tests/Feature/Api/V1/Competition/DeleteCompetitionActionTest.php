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

final class DeleteCompetitionActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_soft_deletes_an_active_competition_for_an_authenticated_user(): void
    {
        $competition = $this->createCompetition([
            'active' => true,
            'from' => '2026-05-10',
            'to' => '2026-05-11',
        ]);
        $user = $this->createUser();
        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/competitions/{$competition->id}")
            ->assertNoContent()
        ;

        $this->assertDatabaseHas('competitions', [
            'id' => $competition->id,
            'active' => false,
            'updated_by' => $user->id,
        ]);
        $this->getJson("/api/v1/competitions/{$competition->id}")->assertNotFound();
        $this->getJson('/api/v1/competitions?year=2026')
            ->assertOk()
            ->assertJsonMissing(['id' => (string) $competition->id])
        ;
    }

    #[Test]
    public function it_requires_authentication_and_returns_not_found_for_inactive_records(): void
    {
        $competition = $this->createCompetition(['active' => false]);

        $this->deleteJson("/api/v1/competitions/{$competition->id}")
            ->assertUnauthorized()
        ;
        Sanctum::actingAs($this->createUser());
        $this->deleteJson("/api/v1/competitions/{$competition->id}")
            ->assertNotFound()
        ;
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
    }

    private function createCompetition(array $attributes = []): Competition
    {
        $model = Competition::factory()->createOne($attributes);

        return Competition::query()->findOrFail($model->getKey());
    }
}
