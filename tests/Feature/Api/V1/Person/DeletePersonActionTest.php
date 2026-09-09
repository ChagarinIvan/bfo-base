<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Person;

use App\Domain\Person\Person;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DeletePersonActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_requires_authentication(): void
    {
        $person = $this->createPerson();

        $this->deleteJson("/api/v1/persons/{$person->id}")->assertUnauthorized();
    }

    #[Test]
    public function it_disables_a_person(): void
    {
        Sanctum::actingAs($this->createUser());
        $person = $this->createPerson();

        $this->deleteJson("/api/v1/persons/{$person->id}")->assertNoContent();

        $this->assertDatabaseHas('person', ['id' => $person->id, 'active' => false]);
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
    }

    private function createPerson(): Person
    {
        /** @var Person $person */
        $person = Person::factory()->createOne();

        return $person;
    }
}
