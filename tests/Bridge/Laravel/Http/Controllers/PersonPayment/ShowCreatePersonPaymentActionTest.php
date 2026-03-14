<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\PersonPayment;

use App\Domain\Person\Person;
use App\Domain\User\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ShowCreatePersonPaymentActionTest extends TestCase
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
     * @see ShowCreatePersonPaymentAction::class
     */
    public function it_shows_create_person_prompt_page(): void
    {
        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        /** @var Person $person */
        $person = Person::factory()->createOne();

        $this->get("/persons/$person->id/payments/create")
            ->assertStatus(Response::HTTP_OK)
            ->assertSee("<input class=\"form-control \" type=\"date\" id=\"date\" name=\"date\"/>", false)
        ;
    }
}
