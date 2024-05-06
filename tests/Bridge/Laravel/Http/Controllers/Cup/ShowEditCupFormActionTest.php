<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Cup;

use App\Bridge\Laravel\Http\Controllers\Cup\ShowEditCupFormAction;
use App\Domain\User\User;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ShowEditCupFormActionTest extends TestCase
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
     * @see ShowEditCupFormAction::class
     */
    public function it_shows_edit_cup_page(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get("/cups/101/edit")
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<input class="form-control" id="name" name="name" value="test master cup">', false)
            ->assertSee('<option value="master" selected>', false)
            ->assertSee('<option value="2022" selected>', false)
            ->assertSee('<input class="form-control" id="eventsCount" name="eventsCount" value="3">', false)
            ->assertSee('<input class="form-check-input" type="checkbox" id="visible" name="visible" checked>', false)
        ;
    }
}
