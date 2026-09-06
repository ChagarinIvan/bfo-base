<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\PersonPayment;

use App\Domain\Person\Person;
use App\Domain\PersonPayment\PersonPayment;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function str_contains;
use function strtolower;

final class ListPersonPaymentsActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_lists_payments_for_an_existing_person(): void
    {
        $this->authenticate();
        $person = $this->createPerson();
        PersonPayment::factory()->createOne([
            'person_id' => $person->id,
            'year' => 2024,
            'date' => '2024-05-10',
        ]);
        PersonPayment::factory()->createOne([
            'person_id' => $person->id,
            'year' => 2025,
            'date' => '2025-03-14',
        ]);

        $this->getJson('/api/v1/persons/payments?personId=' . $person->id)
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment(['year' => '2025', 'date' => '2025-03-14'])
            ->assertJsonFragment(['year' => '2024', 'date' => '2024-05-10'])
            ->assertJsonStructure([
                '*' => ['id', 'personId', 'year', 'date', 'created', 'updated'],
            ])
        ;
    }

    #[Test]
    public function it_returns_empty_list_for_a_person_without_payments(): void
    {
        $this->authenticate();
        $person = $this->createPerson();

        $this->getJson('/api/v1/persons/payments?personId=' . $person->id)
            ->assertOk()
            ->assertExactJson([])
        ;
    }

    #[Test]
    public function it_filters_payments_by_year(): void
    {
        $this->authenticate();
        $person = $this->createPerson();
        PersonPayment::factory()->createOne(['person_id' => $person->id, 'year' => 2024]);
        PersonPayment::factory()->createOne(['person_id' => $person->id, 'year' => 2025]);

        $this->getJson('/api/v1/persons/payments?personId=' . $person->id . '&year=2024')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['year' => '2024'])
        ;
    }

    #[Test]
    public function it_returns_empty_list_for_an_unknown_person(): void
    {
        $this->authenticate();
        $this->getJson('/api/v1/persons/payments?personId=62465')
            ->assertOk()
            ->assertExactJson([])
        ;
    }

    #[Test]
    public function it_requires_authentication(): void
    {
        $this->getJson('/api/v1/persons/payments?personId=62465')->assertUnauthorized();
    }

    #[Test]
    public function it_requires_a_person_id(): void
    {
        $this->authenticate();

        $this->getJson('/api/v1/persons/payments')->assertUnprocessable();
    }

    #[Test]
    public function it_loads_the_list_without_a_payment_n_plus_one(): void
    {
        $this->authenticate();
        $person = $this->createPerson();
        PersonPayment::factory()->createOne(['person_id' => $person->id]);
        $queries = [];

        DB::listen(static function (QueryExecuted $query) use (&$queries): void {
            if (str_contains(strtolower($query->sql), 'persons_payments')) {
                $queries[] = strtolower($query->sql);
            }
        });

        $this->getJson('/api/v1/persons/payments?personId=' . $person->id)->assertOk();

        $this->assertCount(2, $queries);
    }

    private function createPerson(): Person
    {
        /** @var Person $person */
        $person = Person::factory()->createOne(['active' => true]);

        return $person;
    }

    private function authenticate(): void
    {
        $user = SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
        Sanctum::actingAs($user);
    }
}
