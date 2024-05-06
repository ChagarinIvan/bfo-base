<?php

namespace Tests\Bridge\Laravel\Http\Controllers\Cup;

use App\Bridge\Laravel\Http\Controllers\Cup\ShowCreateCupFormAction;
use App\Domain\User\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Symfony\Component\HttpFoundation\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ShowCreateCupFormActionTest extends TestCase
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
     * @see ShowCreateCupFormAction::class
     */
    public function it_shows_create_cup_page(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get('/cups/create')
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<input class="form-control" id="name" name="name">', false)
            ->assertSee('<select class="form-select" id="type" name="type">', false)
            ->assertSee('<option value="sprint"', false)
            ->assertSee('<option value="master"', false)
            ->assertSee('<input class="form-control" id="events_count" name="events_count">', false)
            ->assertSee('<select class="form-select" id="year" name="year">', false)
            ->assertSee('<input class="form-check-input" type="checkbox" id="visible" name="visible" checked>', false)
        ;
    }
}
