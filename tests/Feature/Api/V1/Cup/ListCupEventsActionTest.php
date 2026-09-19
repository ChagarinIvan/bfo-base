<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Cup;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Cup\ListCupEventsAction;
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

/** @see ListCupEventsAction */
final class ListCupEventsActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_a_paginated_public_cup_stage_listing(): void
    {
        /** @var Cup $cup */
        $cup = Cup::factory()->createOne(['id' => 101]);
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne(['id' => 101]);
        /** @var Event $event */
        $event = Event::factory()->createOne([
            'id' => 101,
            'competition_id' => $competition->id,
            'name' => 'Spring stage',
            'date' => '2026-05-10',
        ]);
        CupEvent::factory()->createOne([
            'id' => 101,
            'cup_id' => $cup->id,
            'event_id' => $event->id,
        ]);
        /** @var Event $otherEvent */
        $otherEvent = Event::factory()->createOne([
            'id' => 102,
            'competition_id' => $competition->id,
            'name' => 'Summer stage',
            'date' => '2026-06-10',
        ]);
        CupEvent::factory()->createOne([
            'id' => 102,
            'cup_id' => $cup->id,
            'event_id' => $otherEvent->id,
        ]);

        $this->getJson('/api/v1/cups/101/events?name=Spring&date=2026-05-10&perPage=50')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.cupId', '101')
            ->assertJsonMissingPath('0.event')
            ->assertJsonMissingPath('0.created')
            ->assertHeader('X-Pagination-Per-Page', '50')
        ;
    }

    #[Test]
    public function it_filters_cup_stages_by_event_ids(): void
    {
        /** @var Cup $cup */
        $cup = Cup::factory()->createOne(['id' => 101]);
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne(['id' => 101]);
        /** @var Event $includedEvent */
        $includedEvent = Event::factory()->createOne(['id' => 101, 'competition_id' => $competition->id]);
        /** @var Event $excludedEvent */
        $excludedEvent = Event::factory()->createOne(['id' => 102, 'competition_id' => $competition->id]);
        CupEvent::factory()->createOne(['cup_id' => $cup->id, 'event_id' => $includedEvent->id]);
        CupEvent::factory()->createOne(['cup_id' => $cup->id, 'event_id' => $excludedEvent->id]);

        $this->getJson('/api/v1/cups/101/events?eventIds[]=101')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.eventId', '101')
        ;
    }

    #[Test]
    public function it_validates_event_ids(): void
    {
        $this->getJson('/api/v1/cups/101/events?eventIds[]=invalid')
            ->assertUnprocessable()
            ->assertJsonFragment(['code' => 'validation_error', 'field' => 'eventIds.0'])
        ;
    }

    #[Test]
    public function it_returns_no_stages_for_a_missing_or_inactive_cup(): void
    {
        $this->getJson('/api/v1/cups/999/events')
            ->assertOk()
            ->assertJsonCount(0)
        ;
    }

    #[Test]
    public function it_includes_stage_impressions_only_for_authenticated_clients(): void
    {
        /** @var Cup $cup */
        $cup = Cup::factory()->createOne(['id' => 101]);
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne(['id' => 101]);
        /** @var Event $event */
        $event = Event::factory()->createOne(['id' => 101, 'competition_id' => $competition->id]);
        CupEvent::factory()->createOne(['cup_id' => $cup->id, 'event_id' => $event->id]);

        $this->getJson('/api/v1/cups/101/events')->assertJsonMissingPath('0.created');

        Sanctum::actingAs($this->createUser());
        $this->getJson('/api/v1/cups/101/events')->assertJsonStructure([['created', 'updated']]);
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
    }
}
