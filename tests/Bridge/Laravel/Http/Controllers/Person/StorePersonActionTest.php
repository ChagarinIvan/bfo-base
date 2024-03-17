<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Person;

use App\Bridge\Laravel\Http\Controllers\Person\StorePersonAction;
use App\Domain\User\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class StorePersonActionTest extends TestCase
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
     * @see StorePersonAction::class
     */
    public function it_stores_person(): void
    {
        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->post('/persons/store', [
            'firstname' => 'test name',
            'lastname' => 'test lastname',
            'birthday' => '1989-03-01',
            'clubId' => '1',
        ])
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/persons/1/show')
        ;

        $this->assertDatabaseHas('person', [
            'id' => 1,
            'firstname' => 'test name',
            'created_by' => $user->id,
        ]);
    }
}
