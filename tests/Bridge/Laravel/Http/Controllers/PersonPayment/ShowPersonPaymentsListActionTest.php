<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\PersonPayment;

use App\Domain\Person\Person;
use App\Domain\PersonPayment\PersonPayment;
use App\Domain\User\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
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
     * @test
     * @see ShowPersonPaymentsListAction::class
     */
    public function it_shows_person_payments_list(): void
    {
        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        /** @var Person $person */
        $person = Person::factory()->createOne();
        PersonPayment::factory(state: ['person_id' => $person->id, 'year' => '2025', 'date' => '2025-01-03'])->createOne();
        PersonPayment::factory(state: ['person_id' => $person->id, 'year' => '2024', 'date' => '2024-10-11'])->createOne();

        $this->get("/persons/$person->id/payments")
            ->assertStatus(Response::HTTP_OK)
            ->assertSeeText([
                '2025-01-03',
                '2024-10-11',
            ])
        ;
    }
}
