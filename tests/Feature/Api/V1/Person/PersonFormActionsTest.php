<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Person;

use App\Domain\Person\Citizenship;
use App\Domain\Person\Person;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PersonFormActionsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_requires_authentication_to_create_or_update_a_person(): void
    {
        $person = $this->createPerson();

        $this->postJson('/api/v1/persons', $this->personPayload())->assertUnauthorized();
        $this->putJson("/api/v1/persons/{$person->id}", $this->personPayload())->assertUnauthorized();
    }

    #[Test]
    public function it_creates_a_person_from_the_json_api(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->postJson('/api/v1/persons', $this->personPayload())
            ->assertCreated()
            ->assertJsonPath('lastname', 'Іваноў')
            ->assertJsonPath('firstname', 'Ян')
            ->assertJsonPath('birthday', '2001-06-04')
            ->assertJsonPath('citizenship', Citizenship::BELARUS->value)
            ->assertJsonPath('clubId', null);

        $this->assertDatabaseHas('person', [
            'lastname' => 'Іваноў',
            'firstname' => 'Ян',
            'birthday' => '2001-06-04',
            'citizenship' => Citizenship::BELARUS->value,
            'from_base' => false,
        ]);
    }

    #[Test]
    public function it_updates_a_person_from_the_json_api(): void
    {
        Sanctum::actingAs($this->createUser());
        $person = $this->createPerson([
            'lastname' => 'Стары',
            'firstname' => 'Іван',
            'citizenship' => Citizenship::OTHER,
        ]);

        $this->putJson("/api/v1/persons/{$person->id}", $this->personPayload())
            ->assertOk()
            ->assertJsonPath('id', (string) $person->id)
            ->assertJsonPath('lastname', 'Іваноў')
            ->assertJsonPath('citizenship', Citizenship::BELARUS->value);

        $this->assertDatabaseHas('person', [
            'id' => $person->id,
            'lastname' => 'Іваноў',
            'firstname' => 'Ян',
            'citizenship' => Citizenship::BELARUS->value,
        ]);
    }

    /** @return array<string, string|null> */
    private function personPayload(): array
    {
        return [
            'lastname' => 'Іваноў',
            'firstname' => 'Ян',
            'birthday' => '2001-06-04',
            'clubId' => null,
            'citizenship' => Citizenship::BELARUS->value,
        ];
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
    }

    /** @param array<string, mixed> $attributes */
    private function createPerson(array $attributes = []): Person
    {
        /** @var Person $person */
        $person = Person::factory()->createOne($attributes);

        return $person;
    }
}
