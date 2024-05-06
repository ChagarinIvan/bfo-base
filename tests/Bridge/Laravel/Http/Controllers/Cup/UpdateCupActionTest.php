<?php

namespace Tests\Bridge\Laravel\Http\Controllers\Cup;

use App\Bridge\Laravel\Http\Controllers\Cup\UpdateCupAction;
use App\Domain\User\User;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class UpdateCupActionTest extends TestCase
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
     * @see UpdateCupAction::class
     */
    public function it_updates_cup_data(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->post('/cups/101/update', [
            'name' => 'updated cup',
            'eventsCount' => 3,
            'year' => '2024',
            'type' => 'bike',
            'visible' => false,
        ])
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/cups/101/show')
        ;

        $this->assertDatabaseHas('cups', [
            'name' => 'updated cup',
            'events_count' => 3,
            'year' => 2024,
            'type' => 'bike',
            'updated_by' => $user->id,
            'visible' => true,
            'result' => null,
        ]);
    }
}
