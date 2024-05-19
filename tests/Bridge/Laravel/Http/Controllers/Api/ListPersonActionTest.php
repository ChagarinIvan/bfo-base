<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Api;

use App\Bridge\Laravel\Http\Controllers\Api\ListPersonAction;
use App\Domain\User\User;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ListPersonActionTest extends TestCase
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
     * @see ListPersonAction::class
     */
    public function it_gets_persons_list(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get('/api/persons?withoutLines=1')
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([[
                'id' => 105,
            ]])
        ;
    }
}
