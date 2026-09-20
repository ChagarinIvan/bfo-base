<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Cup;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Cup\CreateCupEventAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Cup\UpdateCupEventAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Cup\ViewCupEventAction;
use App\Domain\Competition\Competition;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Event\Event;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see CreateCupEventAction
 * @see UpdateCupEventAction
 * @see ViewCupEventAction
 */
final class CupEventFormActionsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guests_cannot_use_cup_stage_form_api(): void
    {
        $this->postJson('/api/v1/cup-events', $this->createPayload())->assertUnauthorized();
        $this->putJson('/api/v1/cup-events/101', $this->payload())->assertUnauthorized();
        $this->get('/cups/101/event/create')->assertNotFound();
        $this->get('/cups/101/101/edit')->assertNotFound();
    }

    #[Test]
    public function authenticated_client_can_create_view_and_update_a_cup_stage(): void
    {
        Sanctum::actingAs($this->createUser());
        $cup = Cup::factory()->createOne(['id' => 101]);
        $event = $this->createEvent(101);

        $response = $this->postJson('/api/v1/cup-events', $this->createPayload((int) $cup->getKey(), (int) $event->getKey()))
            ->assertCreated()
            ->assertJsonPath('cupId', (string) $cup->getKey())
            ->assertJsonPath('eventId', (string) $event->getKey())
            ->assertJsonPath('points', '100')
        ;
        $cupEventId = (string) $response->json('id');

        $this->getJson('/api/v1/cup-events/' . $cupEventId)
            ->assertOk()
            ->assertJsonPath('id', $cupEventId)
        ;

        $this->putJson('/api/v1/cup-events/' . $cupEventId, $this->payload((int) $event->getKey(), 75))
            ->assertOk()
            ->assertJsonPath('points', '75')
        ;
    }

    #[Test]
    public function public_clients_can_view_a_cup_stage_without_impressions(): void
    {
        $cup = Cup::factory()->createOne(['id' => 101]);
        $event = $this->createEvent(101);
        $cupEvent = CupEvent::factory()->createOne(['cup_id' => $cup->getKey(), 'event_id' => $event->getKey()]);

        $this->getJson('/api/v1/cup-events/' . $cupEvent->getKey())
            ->assertOk()
            ->assertJsonPath('id', (string) $cupEvent->getKey())
            ->assertJsonMissingPath('created')
        ;
    }

    #[Test]
    public function it_validates_and_rejects_duplicate_stages(): void
    {
        Sanctum::actingAs($this->createUser());
        $cup = Cup::factory()->createOne(['id' => 101]);
        $event = $this->createEvent(101);
        CupEvent::factory()->createOne(['cup_id' => $cup->getKey(), 'event_id' => $event->getKey()]);

        $this->postJson('/api/v1/cup-events', [])->assertUnprocessable()
            ->assertJsonFragment(['field' => 'cupId']);
        $this->postJson('/api/v1/cup-events', $this->createPayload((int) $cup->getKey(), (int) $event->getKey()))->assertUnprocessable()
            ->assertJsonFragment(['code' => 'cup_event_already_exists']);
    }

    /** @return array<string, int> */
    private function createPayload(int $cupId = 101, int $eventId = 101, int $points = 100): array
    {
        return ['cupId' => $cupId, ...$this->payload($eventId, $points)];
    }

    /** @return array<string, int> */
    private function payload(int $eventId = 101, int $points = 100): array
    {
        return ['eventId' => $eventId, 'points' => $points];
    }

    private function createEvent(int $id): Event
    {
        $competition = Competition::factory()->createOne(['id' => $id]);

        /** @var Event $event */
        $event = Event::factory()->createOne(['id' => $id, 'competition_id' => $competition->getKey()]);

        return $event;
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create(['email' => fake()->unique()->safeEmail(), 'password' => Hash::make('secret')]);
    }
}
