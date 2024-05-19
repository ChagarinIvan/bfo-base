<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Person;

use App\Bridge\Laravel\Http\Controllers\Person\DeletePersonAction;
use App\Domain\User\User;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class DeletePersonActionTest extends TestCase
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
     * @see DeletePersonAction::class
     */
    public function it_deletes_person(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get('/persons/101/delete')
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/persons')
        ;

        $this->assertDatabaseHas('person', [
            'id' => 101,
            'active' => false,
            'updated_by' => $user->id,
        ]);

        $this->assertDatabaseMissing('protocol_lines', [
            'person_id' => 101,
        ]);
    }
}
