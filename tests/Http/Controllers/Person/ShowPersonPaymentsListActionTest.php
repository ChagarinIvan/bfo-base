<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Person;

use App\Http\Controllers\Person\ShowPersonPaymentsListAction;
use App\Models\User;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ShowPersonPaymentsListActionTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /**
     * @test
     * @see ShowPersonPaymentsListAction::class
     */
    public function it_shows_person_payments(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        /** @var Authenticatable $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get('/persons/1/payments')
            ->assertStatus(Response::HTTP_OK)
            ->assertSeeTextInOrder([
                '2021',
                '2021-02-12',
                '2022',
                '2022-03-13',
                '2023',
                '2023-01-11',
            ])
        ;
    }
}
