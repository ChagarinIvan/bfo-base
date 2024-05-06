<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\CupEvents;

use App\Bridge\Laravel\Http\Controllers\CupEvents\DeleteCupEventAction;
use App\Domain\User\User;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class DeleteCupEventActionTest extends TestCase
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
     * @see DeleteCupEventAction::class
     */
    public function it_deletes_cup(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get('/cups/101/101/delete')
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/cups/101/show')
        ;

        $this->assertDatabaseHas('cup_events', [
            'id' => 101,
            'active' => false,
            'updated_by' => $user->id,
        ]);
    }
}
