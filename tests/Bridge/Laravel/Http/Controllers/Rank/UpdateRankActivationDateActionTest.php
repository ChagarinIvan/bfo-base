<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Rank;

use App\Bridge\Laravel\Http\Controllers\Rank\UpdateRankActivationDateAction;
use App\Domain\Auth\User;
use App\Domain\Person\PersonRankHistory;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Tests\CreatesApplication;
use Tests\TestCase;

final class UpdateRankActivationDateActionTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createApplication();
        RefreshDatabaseState::$migrated = false;
    }

    /**
     * @see UpdateRankActivationDateAction::class
     */
    #[Test]
    public function it_update_ranks_activation_date(): void
    {
        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->seed(ProtocolLinesSeeder::class);

        ProtocolLine::factory()->createOne(['id' => 107, 'distance_id' => 104, 'complete_rank' => Rank::CandidateMaster->label(), 'person_id' => 102]);
        $line = ProtocolLine::query()->with('distance.event.competition')->findOrFail(107);
        PersonRankHistory::query()->create([
            'person_id' => 102,
            'protocol_line_id' => 107,
            'distance_id' => $line->distance_id,
            'event_id' => $line->distance->event_id,
            'competition_id' => $line->distance->event->competition_id,
            'rank' => Rank::CandidateMaster,
            'change_type' => 'completion',
            'achieved_on' => '2024-02-20',
            'activated_on' => '2024-02-20',
            'started_on' => '2024-02-20',
            'finished_on' => '2026-02-20',
        ]);

        $this->post("/ranks/$line->id/update-activation", ['date' => '2024-02-21'])->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseHas('protocol_lines', [
            'id' => 107,
            'person_id' => 102,
            'activate_rank' => '2024-02-21',
        ]);

        $this->assertDatabaseHas('person_rank_histories', [
            'person_id' => 102,
            'protocol_line_id' => 107,
            'rank' => Rank::CandidateMaster->value,
            'activated_on' => '2024-02-21',
        ]);
    }

    /**
     * @see UpdateRankActivationDateAction::class
     */
    #[Test]
    public function it_removes_ranks_activation_date(): void
    {
        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->seed(ProtocolLinesSeeder::class);

        ProtocolLine::factory()->createOne(['id' => 107, 'distance_id' => 104, 'complete_rank' => Rank::CandidateMaster->label(), 'person_id' => 102]);
        $line = ProtocolLine::query()->with('distance.event.competition')->findOrFail(107);
        PersonRankHistory::query()->create([
            'person_id' => 102,
            'protocol_line_id' => 107,
            'distance_id' => $line->distance_id,
            'event_id' => $line->distance->event_id,
            'competition_id' => $line->distance->event->competition_id,
            'rank' => Rank::CandidateMaster,
            'change_type' => 'completion',
            'achieved_on' => '2024-02-20',
            'activated_on' => '2024-02-20',
            'started_on' => '2024-02-20',
            'finished_on' => '2026-02-20',
        ]);

        $this->post("/ranks/$line->id/update-activation", ['date' => ''])->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseHas('protocol_lines', [
            'id' => 107,
            'person_id' => 102,
            'activate_rank' => null,
        ]);

        $this->assertDatabaseMissing('person_rank_histories', [
            'person_id' => 102,
            'protocol_line_id' => 107,
            'activated_on' => '2024-02-20',
        ]);
    }
}
