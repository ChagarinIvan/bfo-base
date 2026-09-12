<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\ProtocolLine;

use App\Domain\Club\Club;
use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\Person\Person;
use App\Domain\ProtocolLine\ProtocolLine;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function array_filter;
use function str_contains;
use function strtolower;

final class ListProtocolLinesActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_lists_person_protocol_lines_with_event_and_competition(): void
    {
        $person = $this->createPerson();
        $line = $this->createProtocolLine($person, 'Spring Cup', '2026-05-10');

        $this->getJson("/api/v1/protocol-lines?personId={$person->id}&withEvent=1&withCompetition=1")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'personId',
                    'firstname',
                    'lastname',
                    'distanceId',
                    'eventId',
                    'competitionId',
                    'competitionName',
                    'eventName',
                    'eventDate',
                    'groupName',
                    'year',
                    'time',
                    'place',
                    'completeRank',
                ],
            ])
            ->assertJsonPath('0.id', (string) $line->id)
            ->assertJsonPath('0.competitionName', 'Spring Cup')
            ->assertJsonPath('0.eventName', 'Long')
            ->assertJsonPath('0.eventDate', '2026-05-10')
            ->assertJsonPath('0.groupName', 'M21')
        ;
    }

    #[Test]
    public function it_hides_lines_from_inactive_events_and_competitions(): void
    {
        $person = $this->createPerson();
        $activeLine = $this->createProtocolLine($person, 'Active Cup', '2026-05-10');
        $inactiveEventLine = $this->createProtocolLine($person, 'Deleted Event Cup', '2026-05-11');
        $inactiveEventLine->distance->event->active = false;
        $inactiveEventLine->distance->event->save();
        $inactiveCompetitionLine = $this->createProtocolLine($person, 'Deleted Cup', '2026-05-12');
        $inactiveCompetitionLine->distance->event->competition->active = false;
        $inactiveCompetitionLine->distance->event->competition->save();

        $this->getJson("/api/v1/protocol-lines?personId={$person->id}")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', (string) $activeLine->id)
        ;
    }

    #[Test]
    public function it_filters_protocol_lines_by_year_name_and_date(): void
    {
        $person = $this->createPerson();
        $this->createProtocolLine($person, 'Spring Cup', '2026-05-10');
        $this->createProtocolLine($person, 'Winter Cup', '2025-02-11');

        $this->getJson("/api/v1/protocol-lines?personId={$person->id}&withEvent=1&withCompetition=1&year=2026&competitionName=Spring&date=2026-05-10")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.competitionName', 'Spring Cup')
        ;
    }

    #[Test]
    public function it_returns_an_empty_list_for_an_unknown_person(): void
    {
        $this->getJson('/api/v1/protocol-lines?personId=62465')
            ->assertOk()
            ->assertExactJson([])
        ;
    }

    #[Test]
    public function it_returns_an_empty_list_for_an_inactive_person(): void
    {
        $person = $this->createPerson(['active' => false]);

        $this->getJson("/api/v1/protocol-lines?personId={$person->id}")
            ->assertOk()
            ->assertExactJson([])
        ;
    }

    #[Test]
    public function it_requires_person_id(): void
    {
        $this->getJson('/api/v1/protocol-lines')->assertUnprocessable();
    }

    #[Test]
    public function it_validates_person_id(): void
    {
        $this->getJson('/api/v1/protocol-lines?personId=invalid')
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.field', 'personId')
        ;
    }

    #[Test]
    public function it_validates_year(): void
    {
        $person = $this->createPerson();

        $this->getJson("/api/v1/protocol-lines?personId={$person->id}&year=26")
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.field', 'year')
        ;
    }

    #[Test]
    public function it_is_available_without_authentication(): void
    {
        $person = $this->createPerson();
        $this->createProtocolLine($person, 'Public Cup', '2026-06-01');

        $this->getJson("/api/v1/protocol-lines?personId={$person->id}")
            ->assertOk();
    }

    #[Test]
    public function it_scopes_bare_protocol_lines_to_the_selected_distance(): void
    {
        $person = $this->createPerson();
        $selectedLine = $this->createProtocolLine($person, 'Spring Cup', '2026-05-10');
        $this->createProtocolLine($person, 'Summer Cup', '2026-06-10');

        $this->getJson("/api/v1/protocol-lines?distanceId={$selectedLine->distance_id}")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', (string) $selectedLine->id)
            ->assertJsonPath('0.eventId', null)
            ->assertJsonPath('0.competitionId', null)
        ;
    }

    #[Test]
    public function it_orders_selected_distance_protocol_lines_by_protocol_insert_order(): void
    {
        $person = $this->createPerson();
        $firstPlace = $this->createProtocolLine($person, 'Spring Cup', '2026-05-10', [
            'id' => 40,
            'place' => 1,
            'time' => '00:50:00',
        ]);
        ProtocolLine::factory()->createOne([
            'id' => 10,
            'distance_id' => $firstPlace->distance_id,
            'person_id' => $person->id,
            'place' => 1,
            'time' => '01:00:00',
        ]);
        ProtocolLine::factory()->createOne([
            'id' => 5,
            'distance_id' => $firstPlace->distance_id,
            'person_id' => $person->id,
            'place' => 2,
            'time' => '00:30:00',
        ]);
        ProtocolLine::factory()->createOne([
            'id' => 30,
            'distance_id' => $firstPlace->distance_id,
            'person_id' => $person->id,
            'place' => 2,
            'time' => '00:30:00',
        ]);
        ProtocolLine::factory()->createOne([
            'id' => 15,
            'distance_id' => $firstPlace->distance_id,
            'person_id' => $person->id,
            'place' => 0,
            'time' => null,
        ]);
        ProtocolLine::factory()->createOne([
            'id' => 20,
            'distance_id' => $firstPlace->distance_id,
            'person_id' => $person->id,
            'place' => null,
            'time' => null,
        ]);

        $this->getJson("/api/v1/protocol-lines?distanceId={$firstPlace->distance_id}&withClub=1")
            ->assertOk()
            ->assertJsonPath('0.id', '5')
            ->assertJsonPath('1.id', '10')
            ->assertJsonPath('2.id', '15')
            ->assertJsonPath('3.id', '20')
            ->assertJsonPath('4.id', '30')
            ->assertJsonPath('5.id', '40')
        ;
    }

    #[Test]
    public function it_resolves_a_normalized_raw_club_name_when_requested(): void
    {
        $person = $this->createPerson();
        /** @var Club $club */
        $club = Club::factory()->createOne([
            'name' => 'КСА Мінск',
            'normalize_name' => 'ксо мінск',
        ]);
        $line = $this->createProtocolLine(
            $person,
            'Spring Cup',
            '2026-05-10',
            ['club' => '  КСА   Мінск  '],
        );

        $this->getJson("/api/v1/protocol-lines?distanceId={$line->distance_id}&withClub=1")
            ->assertOk()
            ->assertJsonPath('0.club', '  КСА   Мінск  ')
            ->assertJsonPath('0.clubId', (string) $club->id)
            ->assertJsonPath('0.clubName', 'КСА Мінск')
        ;
    }

    #[Test]
    public function it_returns_pagination_headers(): void
    {
        $person = $this->createPerson();
        $this->createProtocolLine($person, 'Spring Cup', '2026-05-10');
        $this->createProtocolLine($person, 'Summer Cup', '2026-06-10');

        $this->getJson("/api/v1/protocol-lines?personId={$person->id}&perPage=1&page=2")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertHeader('X-Pagination-Total', '2')
            ->assertHeader('X-Pagination-Per-Page', '1')
            ->assertHeader('X-Pagination-Current-Page', '2')
        ;
    }

    #[Test]
    public function it_does_not_load_relations_per_protocol_line(): void
    {
        $person = $this->createPerson();
        $this->createProtocolLine($person, 'Spring Cup', '2026-05-10');
        $this->createProtocolLine($person, 'Summer Cup', '2026-06-10');
        $queries = [];

        DB::listen(static function (QueryExecuted $query) use (&$queries): void {
            $queries[] = strtolower($query->sql);
        });

        $this->getJson("/api/v1/protocol-lines?personId={$person->id}&withEvent=1&withCompetition=1")
            ->assertOk();

        $this->assertCount(2, array_filter(
            $queries,
            static fn (string $query): bool => str_contains($query, 'from `protocol_lines`'),
        ));

        foreach (['distances', 'events', 'groups', 'competitions'] as $table) {
            $this->assertCount(1, array_filter(
                $queries,
                static fn (string $query): bool => str_contains($query, "from `{$table}`"),
            ));
        }
    }

    /** @param array<string, mixed> $attributes */
    private function createPerson(array $attributes = []): Person
    {
        /** @var Person $person */
        $person = Person::factory()->createOne($attributes + ['active' => true]);

        return $person;
    }

    /** @param array<string, mixed> $lineAttributes */
    private function createProtocolLine(
        Person $person,
        string $competitionName,
        string $date,
        array $lineAttributes = [],
    ): ProtocolLine
    {
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne([
            'id' => null,
            'name' => $competitionName,
            'from' => $date,
            'to' => $date,
        ]);
        /** @var Event $event */
        $event = Event::factory()->createOne([
            'competition_id' => $competition->id,
            'name' => 'Long',
            'date' => $date,
            'active' => true,
        ]);
        /** @var Group $group */
        $group = Group::factory()->createOne([
            'id' => null,
            'name' => 'M21',
        ]);
        /** @var Distance $distance */
        $distance = Distance::factory()->createOne([
            'id' => null,
            'event_id' => $event->id,
            'group_id' => $group->id,
        ]);
        /** @var ProtocolLine $line */
        $line = ProtocolLine::factory()->createOne([
            'id' => null,
            'distance_id' => $distance->id,
            'person_id' => $person->id,
            ...$lineAttributes,
        ]);

        return $line;
    }
}
