<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Person;

use App\Bridge\Laravel\Http\Controllers\PersonPayment\ShowPersonPaymentsListAction;
use App\Domain\User\User;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ShowPersonPaymentsListActionTest extends TestCase
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
     * @see ShowPersonPaymentsListAction::class
     */
    #[Test]
    public function it_shows_person_payments(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        /** @var Authenticatable $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get('/persons/101/payments')
            ->assertStatus(Response::HTTP_OK)
            ->assertSeeTextInOrder([
                '2023',
                '2023-01-11',
                '2022',
                '2022-01-11',
                '2021',
                '2021-02-12',
            ])
        ;
    }
}
