<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Competition;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Competition\ListCompetitionsAction;
use App\Domain\Competition\Competition;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

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
            ->assertJsonPath('data.0.id', (string) $competition->getKey())
            ->assertJsonMissingPath('data.0.created')
            ->assertJsonMissingPath('data.0.updated')
        ;
    }

    #[Test]
    public function it_includes_audit_fields_for_authenticated_client(): void
    {
        $this->createCompetition();
        Sanctum::actingAs($this->createUser());

        $this->getJson('/api/v1/competitions')
            ->assertOk()
            ->assertJsonStructure(['data' => [['created', 'updated']]])
        ;
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
