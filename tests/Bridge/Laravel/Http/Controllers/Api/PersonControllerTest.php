<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Api;

use App\Bridge\Laravel\Http\Controllers\Api\PersonController;
use App\Domain\User\User;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Tests\CreatesApplication;
use Tests\TestCase;

final class PersonControllerTest extends TestCase
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
     * @see PersonController::index
     */
    public function it_gets_persons_list(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get('/api/person')
            ->assertOk()
            ->assertJsonCount(5)
        ;
    }
}
