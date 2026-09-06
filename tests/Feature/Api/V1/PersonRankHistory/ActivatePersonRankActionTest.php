<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\PersonRankHistory;

use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use App\Infrastructure\Sanctum\SanctumUser;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ActivatePersonRankActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_requires_authentication_and_a_date(): void
    {
        $this->postJson('/api/v1/person-rank-history/107/activation', [])
            ->assertUnauthorized();

        $this->authenticate();
        $this->postJson('/api/v1/person-rank-history/107/activation', [])
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.field', 'date');
    }

    #[Test]
    public function it_activates_a_protocol_line_through_the_existing_service(): void
    {
        $this->authenticate();
        $this->seed(ProtocolLinesSeeder::class);
        ProtocolLine::factory()->createOne([
            'id' => 107,
            'distance_id' => 104,
            'complete_rank' => Rank::CandidateMaster->label(),
            'person_id' => 102,
        ]);

        $this->postJson('/api/v1/person-rank-history/107/activation', [
            'date' => '2024-06-15',
        ])
            ->assertOk()
            ->assertJson(['personId' => '102']);

        $this->assertDatabaseHas('protocol_lines', [
            'id' => 107,
            'person_id' => 102,
            'activate_rank' => '2024-06-15',
        ]);
    }

    #[Test]
    public function it_returns_not_found_for_an_unknown_protocol_line(): void
    {
        $this->authenticate();

        $this->postJson('/api/v1/person-rank-history/62465/activation', [
            'date' => '2024-06-15',
        ])->assertNotFound();
    }

    private function authenticate(): void
    {
        $user = SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
        Sanctum::actingAs($user);
    }
}
