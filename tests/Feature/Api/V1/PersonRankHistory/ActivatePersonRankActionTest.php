<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\PersonRankHistory;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\RebuildPersonRanks;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Person\PersonRankHistory\ActivatePersonRankAction;
use App\Domain\Event\Event;
use App\Domain\Person\Person;
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

/** @see ActivatePersonRankAction */

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
    public function it_accepts_manual_activation_of_first_rank(): void
    {
        $this->authenticate();
        $this->seed(ProtocolLinesSeeder::class);
        Event::query()->whereKey(102)->update(['date' => '2026-01-10']);
        ProtocolLine::factory()->createOne([
            'id' => 107,
            'distance_id' => 104,
            'complete_rank' => Rank::FirstRank->label(),
            'activate_rank' => null,
            'person_id' => 102,
        ]);

        $this->postJson('/api/v1/person-rank-history/107/activation', [
            'date' => '2026-02-01',
        ])->assertOk()->assertJsonPath('activateRank', '2026-02-01');

        $this->assertSame('2026-02-01', ProtocolLine::query()->findOrFail(107)->activate_rank?->toDateString());
        $this->app->make(RebuildPersonRanksService::class)->execute(new RebuildPersonRanks(102, new UserId(1)));
        $this->assertSame(Rank::FirstRank, Person::query()->findOrFail(102)->current_rank);
        $this->assertSame('2026-02-01', PersonRankHistory::query()->where('protocol_line_id', 107)->sole()->activated_on?->toDateString());
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
