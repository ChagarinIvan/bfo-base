<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Event;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Event\ListEventsAction;
use App\Domain\Competition\Competition;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Event\EventProcessingStatus;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** @see ListEventsAction */

final class ListEventsActionTest extends TestCase
{
    use RefreshDatabase;

    /** @return iterable<string, array{EventProcessingStatus}> */
    public static function processingStates(): iterable
    {
        foreach (EventProcessingStatus::cases() as $status) {
            if ($status !== EventProcessingStatus::READY) {
                yield $status->value => [$status];
            }
        }
    }

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
        $this->makeProtocolReady($event);
        $this->createEvent($competition, ['active' => false]);

        $this->getJson("/api/v1/events?competitionId={$competition->id}&withParticipantsCount=1")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', (string) $event->id)
            ->assertJsonPath('0.competitionId', (string) $competition->id)
            ->assertJsonPath('0.participantsCount', 2)
            ->assertJsonPath('0.cups', null)
        ;
    }

    #[Test]
    public function it_returns_pagination_headers(): void
    {
        $competition = $this->createCompetition();
        $this->makeProtocolReady($this->createEvent($competition, ['date' => '2026-05-10']));
        $this->makeProtocolReady($this->createEvent($competition, ['date' => '2026-05-11']));

        $this->getJson("/api/v1/events?competitionId={$competition->id}&perPage=1&page=2")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertHeader('X-Pagination-Has-Next', 'false')
            ->assertHeaderMissing('X-Pagination-Total')
            ->assertHeaderMissing('X-Pagination-Last-Page')
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
    public function it_lists_events_by_ids_with_competition_names(): void
    {
        $competition = $this->createCompetition(['name' => 'Spring Cup']);
        $event = $this->createEvent($competition);
        $this->makeProtocolReady($event);

        $this->getJson("/api/v1/events?ids[]={$event->id}&withCompetition=1")
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=86400, private')
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', (string) $event->id)
            ->assertJsonPath('0.competitionName', 'Spring Cup');
    }

    #[Test]
    public function it_lists_cup_stage_options_by_year_without_requiring_a_group(): void
    {
        $competition = $this->createCompetition(['name' => 'Spring Cup']);
        $event = $this->createEvent($competition, ['date' => '2026-05-11']);

        $this->getJson('/api/v1/events?year=2026&notRelatedToCup=60&perPage=100&withCompetition=1')
            ->assertOk()
            ->assertJsonPath('0.id', (string) $event->id)
            ->assertJsonPath('0.competitionName', 'Spring Cup')
        ;
    }

    #[Test]
    public function it_searches_cup_stage_options_by_competition_or_event_name_before_pagination(): void
    {
        $cup = Cup::factory()->createOne(['id' => 101]);
        self::assertInstanceOf(Cup::class, $cup);
        $namedCompetition = $this->createCompetition(['name' => 'Forest League']);
        $otherCompetition = $this->createCompetition(['name' => 'City League']);
        $byCompetition = $this->createEvent($namedCompetition, ['name' => 'Round One', 'date' => '2026-05-11']);
        $byEvent = $this->createEvent($otherCompetition, ['name' => 'Forest Sprint', 'date' => '2026-05-12']);
        $alreadyRelated = $this->createEvent($otherCompetition, ['name' => 'Forest Relay', 'date' => '2026-05-13']);
        CupEvent::factory()->createOne(['cup_id' => $cup->id, 'event_id' => $alreadyRelated->id]);
        $otherCup = Cup::factory()->createOne(['id' => 102]);
        self::assertInstanceOf(Cup::class, $otherCup);
        CupEvent::factory()->createOne(['cup_id' => $otherCup->id, 'event_id' => $alreadyRelated->id]);
        $this->createEvent($namedCompetition, ['name' => 'Old Round', 'date' => '2025-05-14']);

        $base = "/api/v1/events?year=2026&notRelatedToCup={$cup->id}&withCompetition=1&competitionOrEventName=FoReSt&sortBy=date&sort=desc&perPage=1";

        $this->getJson("{$base}&page=1")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', (string) $byEvent->id)
            ->assertHeader('X-Pagination-Has-Next', 'true');

        $this->getJson("{$base}&page=2")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', (string) $byCompetition->id)
            ->assertHeader('X-Pagination-Has-Next', 'false');

        $this->getJson("/api/v1/events?year=2026&competitionOrEventName=Sprint&sortBy=date&sort=desc")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', (string) $byEvent->id);
    }

    #[Test]
    public function it_rejects_invalid_event_option_sort_and_short_search(): void
    {
        $this->getJson('/api/v1/events?year=2026&sortBy=name&sort=desc')
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.field', 'sortBy');

        $this->getJson('/api/v1/events?year=2026&sortBy=date&sort=sideways')
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.field', 'sort');

        $this->getJson('/api/v1/events?year=2026&competitionOrEventName=ab')
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.field', 'competitionOrEventName');
    }

    #[Test]
    public function it_orders_equal_event_dates_by_id_when_sorting_descending(): void
    {
        $competition = $this->createCompetition();
        $first = $this->createEvent($competition, ['date' => '2026-05-11']);
        $second = $this->createEvent($competition, ['date' => '2026-05-11']);

        $base = "/api/v1/events?competitionId={$competition->id}&sortBy=date&sort=desc&perPage=1";
        $this->getJson("{$base}&page=1")
            ->assertOk()
            ->assertJsonPath('0.id', (string) $first->id);
        $this->getJson("{$base}&page=2")
            ->assertOk()
            ->assertJsonPath('0.id', (string) $second->id);
    }

    #[Test]
    public function it_includes_impressions_for_an_authenticated_client(): void
    {
        $competition = $this->createCompetition();
        $event = $this->createEvent($competition);
        $this->makeProtocolReady($event);
        Sanctum::actingAs($this->createUser());

        $this->getJson("/api/v1/events?competitionId={$competition->id}")
            ->assertOk()
            ->assertJsonStructure([['created', 'updated', 'processingStatus']])
            ->assertJsonPath('0.processingStatus', 'ready')
        ;
    }

    #[Test]
    #[DataProvider('processingStates')]
    public function it_exposes_processing_states_to_authenticated_clients_and_hides_them_from_guests(EventProcessingStatus $status): void
    {
        $competition = $this->createCompetition();
        $event = $this->createEvent($competition, [
            'processing_status' => $status,
            'error_message' => $status->isTerminal() ? 'Processing failed.' : null,
        ]);

        $this->getJson("/api/v1/events?competitionId={$competition->id}")
            ->assertOk()
            ->assertExactJson([]);

        $this->getJson("/api/v1/events/{$event->id}")->assertNotFound();

        Sanctum::actingAs($this->createUser());
        $this->getJson("/api/v1/events?competitionId={$competition->id}")
            ->assertOk()
            ->assertJsonPath('0.id', (string) $event->id)
            ->assertJsonPath('0.processingStatus', $status->value);
        $this->getJson("/api/v1/events/{$event->id}")
            ->assertOk()
            ->assertJsonPath('processingStatus', $status->value)
            ->assertJsonPath('errorMessage', $event->error_message);
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

    private function makeProtocolReady(Event $event): void
    {
        $event->processing_status = EventProcessingStatus::READY;
        $event->save();
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
    }
}
