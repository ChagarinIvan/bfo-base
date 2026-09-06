<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\PersonRankHistory;

use App\Domain\Person\PersonRankHistory;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use App\Infrastructure\Sanctum\SanctumUser;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UpdatePersonRankActivationActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_updates_activation_date(): void
    {
        $this->authenticate();
        $this->createActivatedLine();

        $this->putJson('/api/v1/person-rank-history/107/activation', [
            'date' => '2024-06-20',
        ])
            ->assertOk()
            ->assertJson(['personId' => '102']);
        $this->assertDatabaseHas('protocol_lines', [
            'id' => 107,
            'activate_rank' => '2024-06-20',
        ]);
    }

    #[Test]
    public function it_removes_activation_with_a_null_date(): void
    {
        $this->authenticate();
        $this->createActivatedLine();

        $this->putJson('/api/v1/person-rank-history/107/activation', ['date' => null])
            ->assertOk();
        $this->assertDatabaseHas('protocol_lines', [
            'id' => 107,
            'activate_rank' => null,
        ]);
    }

    #[Test]
    public function it_requires_authentication_and_returns_not_found_for_unknown_line(): void
    {
        $this->putJson('/api/v1/person-rank-history/107/activation', [
            'date' => '2024-06-20',
        ])->assertUnauthorized();

        $this->authenticate();
        $this->putJson('/api/v1/person-rank-history/62465/activation', [
            'date' => '2024-06-20',
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

    private function createActivatedLine(): void
    {
        $this->seed(ProtocolLinesSeeder::class);
        ProtocolLine::factory()->createOne([
            'id' => 107,
            'distance_id' => 104,
            'complete_rank' => Rank::CandidateMaster->label(),
            'person_id' => 102,
            'activate_rank' => '2024-06-15',
        ]);
        $line = ProtocolLine::query()->with('distance.event.competition')->findOrFail(107);
        PersonRankHistory::query()->create([
            'person_id' => 102,
            'protocol_line_id' => 107,
            'distance_id' => $line->distance_id,
            'event_id' => $line->distance->event_id,
            'competition_id' => $line->distance->event->competition_id,
            'rank' => Rank::CandidateMaster,
            'change_type' => 'completion',
            'achieved_on' => '2024-06-01',
            'activated_on' => '2024-06-15',
            'started_on' => '2024-06-15',
            'finished_on' => '2026-06-15',
        ]);
    }
}
