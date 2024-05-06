<?php

namespace Tests\Bridge\Laravel\Http\Controllers\Cup;

use App\Bridge\Laravel\Http\Controllers\Cup\StoreCupAction;
use App\Domain\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Auth\Authenticatable;
use Tests\CreatesApplication;
use Tests\TestCase;

final class StoreCupActionTest extends TestCase
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
     * @see StoreCupAction::class
     */
    public function it_stores_cup(): void
    {
        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->post('/cups/store', [
            'name' => 'test cup',
            'eventsCount' => '3',
            'year' => '2023',
            'type' => 'master',
            'visible' => '1',
        ])
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/cups/1/show')
        ;

        $this->assertDatabaseHas('cups', [
            'name' => 'test cup',
            'events_count' => 3,
            'year' => 2023,
            'type' => 'master',
            'created_by' => $user->id,
            'visible' => true,
            'result' => null,
        ]);
    }
}
