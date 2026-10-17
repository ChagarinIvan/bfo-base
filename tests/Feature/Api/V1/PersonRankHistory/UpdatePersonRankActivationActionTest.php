<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\PersonRankHistory;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\RebuildPersonRanks;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Person\PersonRankHistory\UpdatePersonRankActivationAction;
use App\Domain\Event\Event;
use App\Domain\Person\PersonRankHistory;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use App\Domain\Shared\Clock;
use App\Domain\Shared\FrozenClock;
use App\Infrastructure\Sanctum\SanctumUser;
use Carbon\Carbon;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** @see UpdatePersonRankActivationAction */

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

    #[Test]
    public function it_rejects_correction_to_a_date_before_the_protocol_date(): void
    {
        $this->authenticate();
        $this->createActivatedLine();
        Event::query()->whereKey(102)->update(['date' => '2024-06-01']);

        $this->putJson('/api/v1/person-rank-history/107/activation', ['date' => '2024-05-31'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.code', 'rank_activation_before_achievement');
        $this->assertSame('2024-06-15', ProtocolLine::query()->findOrFail(107)->activate_rank?->toDateString());

        $this->putJson('/api/v1/person-rank-history/107/activation', ['date' => '2024-06-01'])
            ->assertOk()
            ->assertJsonPath('activateRank', '2024-06-01');
    }

    #[Test]
    public function clearing_a_new_lower_rank_manual_date_restores_the_monthly_wait(): void
    {
        $this->authenticate();
        $this->seed(ProtocolLinesSeeder::class);
        Event::query()->whereKey(102)->update(['date' => '2026-01-10']);
        ProtocolLine::factory()->createOne([
            'id' => 107,
            'distance_id' => 104,
            'complete_rank' => Rank::SecondRank->label(),
            'activate_rank' => '2026-01-20',
            'delayed_rank_activation' => true,
            'person_id' => 102,
        ]);
        $this->app->instance(Clock::class, new FrozenClock(Carbon::parse('2026-01-25')));

        $this->putJson('/api/v1/person-rank-history/107/activation', ['date' => null])->assertOk();
        $this->app->make(RebuildPersonRanksService::class)->execute(new RebuildPersonRanks(102, new UserId(1)));

        $this->assertNull(ProtocolLine::query()->findOrFail(107)->activate_rank);
        $this->assertNull(PersonRankHistory::query()->where('protocol_line_id', 107)->sole()->activated_on);
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
