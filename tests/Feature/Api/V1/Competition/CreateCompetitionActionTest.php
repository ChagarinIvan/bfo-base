<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Competition;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Competition\CreateCompetitionAction;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** @see CreateCompetitionAction */
final class CreateCompetitionActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_requires_authentication(): void
    {
        $this->postJson('/api/v1/competitions', $this->payload())->assertUnauthorized();
    }

    #[Test]
    public function authenticated_user_can_create_competition(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->postJson('/api/v1/competitions', $this->payload())
            ->assertCreated()
            ->assertJsonPath('name', 'New competition')
        ;
    }

    #[Test]
    public function it_rejects_an_end_date_before_the_start_date(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->postJson('/api/v1/competitions', [
            ...$this->payload(),
            'to' => '2025-12-31',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.code', 'validation_error')
            ->assertJsonPath('errors.0.field', 'to')
        ;
    }

    #[Test]
    public function it_rejects_missing_required_fields(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->postJson('/api/v1/competitions', [])
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.code', 'validation_error')
            ->assertJsonFragment(['field' => 'name'])
            ->assertJsonFragment(['field' => 'description'])
            ->assertJsonFragment(['field' => 'from'])
            ->assertJsonFragment(['field' => 'to'])
        ;
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return [
            'name' => 'New competition',
            'description' => 'Description',
            'from' => '2026-01-01',
            'to' => '2026-01-02',
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
