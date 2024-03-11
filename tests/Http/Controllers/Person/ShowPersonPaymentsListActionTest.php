<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Person;

use App\Http\Controllers\Person\ShowPersonPaymentsListAction;
use Database\Seeders\ProtocolLinesSeeder;
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

        $this
            ->get('persons/1/payments')
            ->assertStatus(Response::HTTP_OK)
            ->assertSeeTextInOrder([
                '<td>2021</td>',
                '<td>2021-01-11</td>',
                '<td>2022</td>',
                '<td>2022-01-11</td>',
                '<td>2023</td>',
                '<td>2023-01-11</td>',
            ])
        ;
    }
}
