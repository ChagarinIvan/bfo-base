<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Event;

use App\Bridge\Laravel\Http\Controllers\Event\DeleteEventAction;
use App\Domain\User\User;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class DeleteEventActionTest extends TestCase
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
     * @see DeleteEventAction::class
     */
    public function it_deletes_event(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get("/events/101/delete")
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/competitions/1/show');
        ;

        $this->assertDatabaseHas('events', [
            'id' => 101,
            'active' => false,
            'updated_by' => $user->id,
        ]);

        $this->assertDatabaseMissing('distances', ['id' => 101,]);
        $this->assertDatabaseMissing('protocol_lines', ['id' => 101,]);
    }
}
