<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Event;

use App\Domain\Competition\Competition;
use App\Domain\Event\Event;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EventManagementActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function mutations_require_authentication(): void
    {
        $competition = Competition::factory()->createOne();
        $event = Event::factory()->createOne(['competition_id' => $competition->id]);

        $this->postJson("/api/v1/competitions/{$competition->getKey()}/events", [])->assertUnauthorized();
        $this->putJson("/api/v1/events/{$event->getKey()}", [])->assertUnauthorized();
        $this->deleteJson("/api/v1/events/{$event->getKey()}")->assertUnauthorized();
        $this->postJson("/api/v1/competitions/{$competition->getKey()}/events/unite", [])->assertUnauthorized();
    }

    #[Test]
    public function it_validates_event_creation_before_processing_a_protocol(): void
    {
        Sanctum::actingAs($this->createUser());
        $competition = Competition::factory()->createOne();

        $this->postJson("/api/v1/competitions/{$competition->getKey()}/events", [])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'name'])
            ->assertJsonFragment(['field' => 'description'])
            ->assertJsonFragment(['field' => 'date'])
        ;
    }

    #[Test]
    public function it_updates_and_deactivates_an_event(): void
    {
        $user = $this->createUser();
        Sanctum::actingAs($user);
        $competition = Competition::factory()->createOne();
        $event = Event::factory()->createOne([
            'competition_id' => $competition->getKey(),
            'active' => true,
        ]);

        $this->putJson("/api/v1/events/{$event->getKey()}", [
            'name' => 'Updated stage',
            'description' => 'Updated description',
            'date' => '2026-05-11',
        ])
            ->assertOk()
            ->assertJsonPath('id', (string) $event->getKey())
            ->assertJsonPath('name', 'Updated stage')
        ;

        $this->deleteJson("/api/v1/events/{$event->getKey()}")->assertNoContent();
        $this->assertDatabaseHas('events', [
            'id' => $event->getKey(),
            'active' => false,
            'updated_by' => $user->id,
        ]);
    }

    #[Test]
    public function legacy_event_web_routes_are_not_registered_while_public_event_reads_remain_available(): void
    {
        $competition = Competition::factory()->createOne();
        $event = Event::factory()->createOne(['competition_id' => $competition->getKey()]);

        $this->get("/events/{$competition->getKey()}/create")->assertNotFound();
        $this->getJson("/api/v1/events/{$event->getKey()}")
            ->assertOk()
            ->assertJsonPath('id', (string) $event->getKey())
        ;
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => 'secret',
        ]);
    }
}
