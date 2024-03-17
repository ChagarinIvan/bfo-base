<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Event;

use App\Bridge\Laravel\Http\Controllers\Event\ShowEditEventFormAction;
use App\Domain\User\User;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ShowEditEventFormActionTest extends TestCase
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
     * @see ShowEditEventFormAction::class
     */
    public function it_shows_edit_competition_page(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        /** @var Authenticatable $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get("/events/3/edit")
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<input class="form-control" id="name" name="name" value="name3" />', false)
            ->assertSee('<input class="form-control" id="description" name="description" value="test3" />', false)
        ;
    }
}
