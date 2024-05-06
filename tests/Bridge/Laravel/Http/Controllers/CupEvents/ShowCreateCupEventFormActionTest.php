<?php

namespace Tests\Bridge\Laravel\Http\Controllers\CupEvents;

use App\Bridge\Laravel\Http\Controllers\CupEvents\ShowCreateCupEventFormAction;
use App\Domain\User\User;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ShowCreateCupEventFormActionTest extends TestCase
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
     * @see ShowCreateCupEventFormAction::class
     */
    public function it_shows_create_cup_event_page(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get('/cups/101/event/create')
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<b class="text-decoration-none text-dark">М35</b>', false)
            ->assertSee('<b class="text-decoration-none text-dark">Ж80</b>', false)
            ->assertSee('<form method="POST" action="http://localhost/cups/101/event/store">', false)
            ->assertSee('<option value="101">2022-01-01 - test - name1</option>', false)
            ->assertSee('<input class="form-control " id="points" name="points" value="1000">', false)
        ;
    }
}
