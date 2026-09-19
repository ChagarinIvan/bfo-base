<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Cup;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Cup\CreateCupAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Cup\UpdateCupAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Cup\ViewCupAction;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupType;
use App\Infrastructure\Sanctum\SanctumUser;
use App\Models\Year;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see CreateCupAction
 * @see UpdateCupAction
 */
final class CupFormActionsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function unauthenticated_clients_cannot_use_cup_form_api(): void
    {
        $this->postJson('/api/v1/cups', $this->payload())->assertUnauthorized();
        $this->putJson('/api/v1/cups/101', $this->payload())->assertUnauthorized();
    }

    #[Test]
    public function authenticated_client_can_create_a_cup(): void
    {
        $user = $this->createUser();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/cups', $this->payload())
            ->assertCreated()
            ->assertJsonPath('name', 'SPA cup')
            ->assertJsonPath('eventsCount', '4')
            ->assertJsonPath('visible', true)
            ->assertJsonStructure(['id', 'groups', 'created', 'updated'])
        ;

        $this->assertDatabaseHas('cups', [
            'name' => 'SPA cup',
            'created_by' => $user->getKey(),
        ]);
    }

    #[Test]
    public function visibility_defaults_to_true_when_omitted_on_create(): void
    {
        Sanctum::actingAs($this->createUser());
        $payload = $this->payload();
        unset($payload['visible']);

        $this->postJson('/api/v1/cups', $payload)
            ->assertCreated()
            ->assertJsonPath('visible', true)
        ;
    }

    #[Test]
    public function it_validates_create_and_update_payloads(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->postJson('/api/v1/cups', [])->assertUnprocessable()
            ->assertJsonFragment(['field' => 'name']);

        $cup = $this->createCup();
        $this->putJson('/api/v1/cups/' . $cup->getKey(), [
            ...$this->payload(),
            'eventsCount' => 0,
        ])->assertUnprocessable()
            ->assertJsonFragment(['field' => 'eventsCount']);

        $this->assertDatabaseHas('cups', [
            'id' => $cup->getKey(),
            'events_count' => 3,
        ]);
    }

    #[Test]
    public function authenticated_client_can_view_and_update_a_cup(): void
    {
        Sanctum::actingAs($this->createUser());
        $cup = $this->createCup();

        $this->getJson('/api/v1/cups/' . $cup->getKey())
            ->assertOk()
            ->assertJsonPath('id', (string) $cup->getKey())
            ->assertJsonPath('eventsCount', (string) $cup->events_count)
        ;

        $this->putJson('/api/v1/cups/' . $cup->getKey(), [
            ...$this->payload(),
            'name' => 'Updated SPA cup',
            'visible' => false,
        ])->assertOk()
            ->assertJsonPath('name', 'Updated SPA cup')
            ->assertJsonPath('visible', false)
        ;

        $this->assertDatabaseHas('cups', [
            'id' => $cup->getKey(),
            'name' => 'Updated SPA cup',
            'events_count' => 4,
            'visible' => false,
        ]);
    }

    #[Test]
    public function it_returns_not_found_for_an_unknown_cup(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->getJson('/api/v1/cups/999999')->assertNotFound();
        $this->putJson('/api/v1/cups/999999', $this->payload())->assertNotFound();
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return [
            'name' => 'SPA cup',
            'eventsCount' => 4,
            'year' => Year::y2026->value,
            'type' => CupType::MASTER->value,
            'visible' => true,
        ];
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
    }

    private function createCup(): Cup
    {
        /** @var Cup $cup */
        $cup = Cup::factory()->createOne([
            'id' => ((int) Cup::query()->max('id')) + 1,
            'type' => CupType::MASTER,
            'year' => Year::y2026,
            'events_count' => 3,
        ]);

        return $cup;
    }
}
