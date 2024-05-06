<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\CupEvents;

use App\Bridge\Laravel\Http\Controllers\CupEvents\ShowEditCupEventFormAction;
use App\Domain\User\User;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ShowEditCupEventFormActionTest extends TestCase
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
     * @see ShowEditCupEventFormAction::class
     */
    public function it_shows_create_cup_event_page(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get('/cups/101/101/edit')
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<form method="POST" action="http://localhost/cups/101/101/update">', false)
            ->assertSee('<option value="101" >2022-01-01 - test - name1</option>', false)
            ->assertSee('<option value="102" selected>2022-03-02 - test - name2</option>', false)
            ->assertSee('<input class="form-control " id="points" name="points" value="1001">', false)
        ;
    }
}
