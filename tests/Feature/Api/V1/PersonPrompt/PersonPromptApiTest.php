<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\PersonPrompt;

use App\Domain\Person\Person;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PersonPromptApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_lists_prompts_with_pagination_for_authenticated_users(): void
    {
        $this->authenticate();
        $person = $this->createPerson();
        PersonPrompt::factory()->createOne(['person_id' => $person->id, 'prompt' => 'first']);
        PersonPrompt::factory()->createOne(['person_id' => $person->id, 'prompt' => 'second']);

        $this->getJson("/api/v1/person-prompts?personId={$person->id}&perPage=1")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonStructure(['0' => ['id', 'personId', 'prompt', 'metaphone']])
            ->assertHeader('X-Pagination-Total', '2');
    }

    #[Test]
    public function it_supports_authenticated_create_update_and_delete(): void
    {
        $this->authenticate();
        $person = $this->createPerson();

        $created = $this->postJson("/api/v1/persons/{$person->id}/prompts", ['prompt' => '  new value  '])
            ->assertCreated()
            ->assertJsonPath('personId', (string) $person->id)
            ->json();

        $this->putJson("/api/v1/person-prompts/{$created['id']}", ['prompt' => 'updated'])
            ->assertOk()
            ->assertJsonPath('prompt', 'updated');

        $this->deleteJson("/api/v1/person-prompts/{$created['id']}")
            ->assertNoContent();

        $this->assertDatabaseHas('persons_prompt', [
            'id' => $created['id'],
            'active' => false,
        ]);

        $this->getJson("/api/v1/person-prompts/{$created['id']}")
            ->assertNotFound();
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
