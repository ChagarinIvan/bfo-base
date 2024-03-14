<?php

declare(strict_types=1);

namespace Bridge\Laravel\Http\Controllers\Competition;

use App\Bridge\Laravel\Http\Controllers\Competition\StoreCompetitionAction;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Symfony\Component\HttpFoundation\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class StoreCompetitionActionTest extends TestCase
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
     * @see StoreCompetitionAction::class
     */
    public function it_stores_competition(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->post('/competitions/store', [
            'name' => 'test competition',
            'description' => 'test competition description',
            'from' => '2023-01-01',
            'to' => '2023-01-02',
        ])
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/competitions/1/show');
        ;
    }
}
