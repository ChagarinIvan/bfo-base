<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Competition;

use App\Bridge\Laravel\Http\Controllers\Competition\DeleteCompetitionAction;
use App\Domain\User\User;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class DeleteCompetitionActionTest extends TestCase
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
     * @see DeleteCompetitionAction::class
     */
    public function it_deletes_competition(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get("/competitions/2021/1/delete")
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/competitions?year=2021')
        ;

        $this->assertDatabaseHas('competitions', [
            'id' => 1,
            'active' => false,
            'updated_by' => $user->id,
        ]);

        $this->assertDatabaseHas('events', [
            'id' => 1,
            'active' => false,
            'updated_by' => $user->id,
        ]);
    }
}
