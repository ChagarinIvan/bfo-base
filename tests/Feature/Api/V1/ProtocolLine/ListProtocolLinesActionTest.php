<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\ProtocolLine;

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
            if (str_contains(strtolower($query->sql), 'protocol_lines')) {
                $queries[] = $query->sql;
            }
        });

        $this->getJson("/api/v1/protocol-lines?personId={$person->id}&withEvent=1&withCompetition=1")
            ->assertOk();

        $this->assertCount(2, $queries);
    }

    /** @param array<string, mixed> $attributes */
    private function createPerson(array $attributes = []): Person
    {
        /** @var Person $person */
        $person = Person::factory()->createOne($attributes + ['active' => true]);

        return $person;
    }

    private function createProtocolLine(Person $person, string $competitionName, string $date): ProtocolLine
    {
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne([
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
        $group = Group::factory()->createOne(['name' => 'M21']);
        /** @var Distance $distance */
        $distance = Distance::factory()->createOne([
            'event_id' => $event->id,
            'group_id' => $group->id,
        ]);
        /** @var ProtocolLine $line */
        $line = ProtocolLine::factory()->createOne([
            'distance_id' => $distance->id,
            'person_id' => $person->id,
        ]);

        return $line;
    }
}
