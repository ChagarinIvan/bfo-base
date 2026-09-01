<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Event;

use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ListEventsActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_requires_a_camel_case_competition_id(): void
    {
        $this->getJson('/api/v1/events')
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.field', 'competitionId')
        ;
        $this->getJson('/api/v1/events?competition_id=1')
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.field', 'competitionId')
        ;
        $this->getJson('/api/v1/events?competitionId=0')
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.field', 'competitionId')
        ;
    }

    #[Test]
    public function it_returns_active_events_with_protocol_line_counts(): void
    {
        $competition = $this->createCompetition();
        $event = $this->createEvent($competition, ['date' => '2026-05-11']);
        $this->createProtocolLine($event);
        $this->createProtocolLine($event);
        $this->createEvent($competition, ['active' => false]);

        $this->getJson("/api/v1/events?competitionId={$competition->id}")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', (string) $event->id)
            ->assertJsonPath('0.competitionId', (string) $competition->id)
            ->assertJsonPath('0.participantsCount', 2)
            ->assertJsonMissingPath('0.flags')
            ->assertJsonMissingPath('0.cups')
        ;
    }

    #[Test]
    public function it_returns_pagination_headers(): void
    {
        $competition = $this->createCompetition();
        $this->createEvent($competition, ['date' => '2026-05-10']);
        $this->createEvent($competition, ['date' => '2026-05-11']);

        $this->getJson("/api/v1/events?competitionId={$competition->id}&perPage=1&page=2")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertHeader('X-Pagination-Total', '2')
            ->assertHeader('X-Pagination-Per-Page', '1')
            ->assertHeader('X-Pagination-Current-Page', '2')
        ;
    }

    #[Test]
    public function it_returns_an_empty_array_when_the_competition_has_no_active_events(): void
    {
        $competition = $this->createCompetition();

        $this->getJson("/api/v1/events?competitionId={$competition->id}")
            ->assertOk()
            ->assertExactJson([])
        ;
    }

    #[Test]
    public function it_returns_an_empty_array_when_the_competition_is_missing_or_inactive(): void
    {
        $inactiveCompetition = $this->createCompetition(['active' => false]);

        $this->getJson('/api/v1/events?competitionId=999999')
            ->assertOk()
            ->assertExactJson([])
        ;
        $this->getJson("/api/v1/events?competitionId={$inactiveCompetition->id}")
            ->assertOk()
            ->assertExactJson([])
        ;
    }

    #[Test]
    public function it_includes_impressions_for_an_authenticated_client(): void
    {
        $competition = $this->createCompetition();
        $this->createEvent($competition);
        Sanctum::actingAs($this->createUser());

        $this->getJson("/api/v1/events?competitionId={$competition->id}")
            ->assertOk()
            ->assertJsonStructure([['created', 'updated']])
        ;
    }

    /** @param array<string, mixed> $attributes */
    private function createCompetition(array $attributes = []): Competition
    {
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne([
            'from' => '2026-05-10',
            'to' => '2026-05-12',
            ...$attributes,
        ]);

        return $competition;
    }

    /** @param array<string, mixed> $attributes */
    private function createEvent(Competition $competition, array $attributes = []): Event
    {
        /** @var Event $event */
        $event = Event::factory()->createOne([
            'competition_id' => $competition->id,
            'date' => '2026-05-10',
            'active' => true,
            ...$attributes,
        ]);

        return $event;
    }

    private function createProtocolLine(Event $event): ProtocolLine
    {
        /** @var Distance $distance */
        $distance = Distance::factory()->createOne([
            'id' => (int) Distance::query()->max('id') + 1,
            'event_id' => $event->id,
        ]);
        /** @var ProtocolLine $protocolLine */
        $protocolLine = ProtocolLine::factory()->createOne([
            'id' => (int) ProtocolLine::query()->max('id') + 1,
            'distance_id' => $distance->id,
        ]);

        return $protocolLine;
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
    }
}
