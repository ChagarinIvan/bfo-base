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

final class RebuildPersonRanksActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_requires_authentication(): void
    {
        $person = Person::factory()->createOne();

        $this->postJson("/api/v1/persons/{$person->getKey()}/ranks/rebuild")
            ->assertUnauthorized();
    }

    #[Test]
    public function it_rebuilds_one_person(): void
    {
        Sanctum::actingAs($this->createUser());
        $person = Person::factory()->createOne();

        $this->postJson("/api/v1/persons/{$person->getKey()}/ranks/rebuild")
            ->assertNoContent();
    }

    #[Test]
    public function it_returns_not_found_for_a_missing_person(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->postJson('/api/v1/persons/999999/ranks/rebuild')
            ->assertNotFound();
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
    }
}
