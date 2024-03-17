<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Club;

use App\Bridge\Laravel\Http\Controllers\Club\StoreClubsAction;
use App\Domain\Club\Club;
use App\Domain\User\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Symfony\Component\HttpFoundation\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class StoreClubsActionTest extends TestCase
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
     * @see StoreClubsAction::class
     */
    public function it_stores_club(): void
    {
        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->post('/clubs/store', [
            'name' => 'test club',
        ])
            ->assertStatus(Response::HTTP_FOUND)
        ;

        $this->assertDatabaseHas('club', [
            'name' => 'test club',
            'active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    /**
     * @test
     * @see StoreClubsAction::class
     */
    public function it_fails_when_club_with_same_name_already_exists(): void
    {
        /** @var Authenticatable|User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        Club::factory()->createOne(['name' => 'test club']);

        $this->post('/clubs/store', [
            'name' => 'test club',
        ])
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/500')
        ;
    }
}
