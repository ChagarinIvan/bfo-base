<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Rank;

use App\Bridge\Laravel\Http\Controllers\Rank\ActivatePersonRankAction;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use App\Domain\User\User;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ActivatePersonRankActionTest extends TestCase
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
     * @test
     * @see ActivatePersonRankAction::class
     */
    public function it_activates_person_rank(): void
    {
        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->seed(ProtocolLinesSeeder::class);

        ProtocolLine::factory()->createOne(['id' => 107, 'distance_id' => 104, 'complete_rank' => Rank::SMC_RANK, 'person_id' => 102]);
        /** @var Rank $rank */
        $rank = Rank::factory()->createOne(['person_id' => 102, 'rank' => Rank::SMC_RANK, 'event_id' => 102, 'activated_date' => null]);

        $this->post("/ranks/$rank->id/activate", ['date' => '2024-02-20'])->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseHas('protocol_lines', [
            'id' => 107,
            'person_id' => 102,
            'activate_rank' => '2024-02-20',
        ]);

        $this->assertDatabaseHas('ranks', [
            'person_id' => 102,
            'event_id' => 102,
            'rank' => Rank::SMC_RANK,
            'start_date' => '2024-02-20',
        ]);
    }
}
