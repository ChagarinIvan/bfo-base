<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\PersonPayment;

use App\Bridge\Laravel\Http\Controllers\Club\StoreClubsAction;
use App\Domain\Club\Club;
use App\Domain\Person\Person;
use App\Domain\User\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Symfony\Component\HttpFoundation\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class StorePersonPaymentActionTest extends TestCase
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
     * @see StorePersonPaymentAction::class
     */
    public function it_stores_person_payment(): void
    {
        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        /** @var Person $person */
        $person = Person::factory()->createOne();

        $this->post("/persons/$person->id/payments/store", [
            'personId' => (string) $person->id,
            'date' => '2025-03-14',
        ]);

        $this->assertDatabaseHas('persons_payments', [
            'person_id' => $person->id,
            'year' => '2025',
            'date' => '2025-03-14',
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
