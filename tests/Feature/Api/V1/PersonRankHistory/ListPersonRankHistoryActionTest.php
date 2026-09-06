<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\PersonRankHistory;

use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRankHistory;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ListPersonRankHistoryActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_lists_raw_history_fields_for_an_active_person(): void
    {
        $person = $this->createPerson();
        $line = $this->createProtocolLine($person);
        $history = PersonRankHistory::query()->create([
            'person_id' => $person->id,
            'protocol_line_id' => $line->id,
            'distance_id' => $line->distance_id,
            'event_id' => $line->distance->event_id,
            'competition_id' => $line->distance->event->competition_id,
            'rank' => Rank::CandidateMaster,
            'change_type' => 'completion',
            'achieved_on' => '2024-06-01',
            'activated_on' => '2024-06-15',
            'started_on' => '2024-06-15',
            'finished_on' => '2026-06-15',
        ]);

        $this->getJson("/api/v1/persons/{$person->id}/rank-histories")
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'id', 'personId', 'protocolLineId', 'distanceId', 'eventId',
                    'competitionId', 'rankId', 'changeType', 'achievedOn',
                    'activatedOn', 'startedOn', 'finishedOn',
                ],
            ])
            ->assertJsonPath('0.id', (string) $history->id)
            ->assertJsonPath('0.personId', (string) $person->id)
            ->assertJsonPath('0.protocolLineId', (string) $line->id)
            ->assertJsonMissingPath('0.eventName')
            ->assertJsonMissingPath('0.competitionName');
    }

    #[Test]
    public function it_returns_an_empty_list_for_an_unknown_or_inactive_person(): void
    {
        $this->getJson('/api/v1/persons/62465/rank-histories')
            ->assertOk()
            ->assertExactJson([]);

        $person = $this->createPerson(['active' => false]);
        $line = $this->createProtocolLine($person);
        PersonRankHistory::query()->create([
            'person_id' => $person->id,
            'protocol_line_id' => $line->id,
            'distance_id' => $line->distance_id,
            'event_id' => $line->distance->event_id,
            'competition_id' => $line->distance->event->competition_id,
            'rank' => Rank::CandidateMaster,
            'change_type' => 'completion',
            'achieved_on' => '2024-06-01',
            'started_on' => '2024-06-15',
        ]);

        $this->getJson("/api/v1/persons/{$person->id}/rank-histories")
            ->assertOk()
            ->assertExactJson([]);
    }

    #[Test]
    public function it_returns_an_empty_list_for_an_invalid_person_id(): void
    {
        $this->getJson('/api/v1/persons/invalid/rank-histories')
            ->assertOk()
            ->assertExactJson([]);
    }

    /** @param array<string, mixed> $attributes */
    private function createPerson(array $attributes = []): Person
    {
        /** @var Person $person */
        $person = Person::factory()->createOne([
            'id' => (int) Person::query()->max('id') + 1,
            'active' => true,
            ...$attributes,
        ]);

        return $person;
    }

    private function createProtocolLine(Person $person): ProtocolLine
    {
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne([
            'id' => (int) Competition::query()->max('id') + 1,
            'from' => '2024-05-20',
            'to' => '2024-05-20',
        ]);
        /** @var Event $event */
        $event = Event::factory()->createOne([
            'competition_id' => $competition->id,
            'date' => '2024-05-20',
            'active' => true,
        ]);
        /** @var Group $group */
        $group = Group::factory()->createOne([
            'id' => (int) Group::query()->max('id') + 1,
        ]);
        /** @var Distance $distance */
        $distance = Distance::factory()->createOne([
            'id' => (int) Distance::query()->max('id') + 1,
            'event_id' => $event->id,
            'group_id' => $group->id,
        ]);

        /** @var ProtocolLine $line */
        $line = ProtocolLine::factory()->createOne([
            'id' => (int) ProtocolLine::query()->max('id') + 1,
            'distance_id' => $distance->id,
            'person_id' => $person->id,
        ]);

        return $line;
    }
}
