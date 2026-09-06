<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\PersonPayment;

use App\Domain\Person\Person;
use App\Domain\PersonPayment\Event\PersonPaymentCreated;
use App\Domain\PersonPayment\Event\PersonPaymentUpdated;
use App\Domain\PersonPayment\PersonPayment;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CreateOrUpdatePersonPaymentActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_requires_authentication(): void
    {
        $person = $this->createPerson();

        $this->postJson('/api/v1/persons/payments', [
            'personId' => $person->id,
            'date' => '2025-03-14',
        ])->assertUnauthorized();
    }

    #[Test]
    public function it_validates_the_payment_date(): void
    {
        $this->authenticate();
        $person = $this->createPerson();

        $this->postJson('/api/v1/persons/payments', [
            'personId' => $person->id,
            'date' => '14.03.2025',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.field', 'date')
        ;
    }

    #[Test]
    public function it_requires_person_id_in_the_request_body(): void
    {
        $this->authenticate();

        $this->postJson('/api/v1/persons/payments', [
            'date' => '2025-03-14',
        ])->assertUnprocessable();
    }

    #[Test]
    public function it_creates_a_payment_with_person_id_in_the_body(): void
    {
        $user = $this->authenticate();
        $person = $this->createPerson();
        Event::fake([PersonPaymentCreated::class]);

        $this->postJson('/api/v1/persons/payments', [
            'personId' => $person->id,
            'date' => '2025-03-14',
        ])
            ->assertCreated()
            ->assertJsonPath('personId', (string) $person->id)
            ->assertJsonPath('year', '2025')
            ->assertJsonPath('date', '2025-03-14')
        ;

        $this->assertDatabaseHas('persons_payments', [
            'person_id' => $person->id,
            'year' => 2025,
            'date' => '2025-03-14',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
        Event::assertDispatched(PersonPaymentCreated::class);
    }

    #[Test]
    public function it_updates_the_existing_payment_for_the_same_year(): void
    {
        $this->authenticate();
        $person = $this->createPerson();
        /** @var PersonPayment $payment */
        $payment = PersonPayment::factory()->createOne([
            'person_id' => $person->id,
            'year' => 2025,
            'date' => '2025-03-14',
        ]);
        Event::fake([PersonPaymentUpdated::class]);

        $this->postJson('/api/v1/persons/payments', [
            'personId' => $person->id,
            'date' => '2025-04-20',
        ])
            ->assertCreated()
            ->assertJsonPath('id', (string) $payment->id)
            ->assertJsonPath('date', '2025-04-20')
        ;

        $this->assertDatabaseCount('persons_payments', 1);
        $this->assertDatabaseHas('persons_payments', [
            'id' => $payment->id,
            'date' => '2025-04-20',
        ]);
        Event::assertDispatched(PersonPaymentUpdated::class);
    }

    #[Test]
    public function it_returns_not_found_for_an_unknown_person(): void
    {
        $this->authenticate();

        $this->postJson('/api/v1/persons/payments', [
            'personId' => 62465,
            'date' => '2025-03-14',
        ])->assertNotFound();
    }

    private function createPerson(): Person
    {
        /** @var Person $person */
        $person = Person::factory()->createOne(['active' => true]);

        return $person;
    }

    private function authenticate(): SanctumUser
    {
        $user = SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
        Sanctum::actingAs($user);

        return $user;
    }
}
