<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Person;

use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\Person\Person;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ExtractPersonActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_requires_authentication(): void
    {
        $this->postJson('/api/v1/protocol-lines/1/extract-person')->assertUnauthorized();
    }

    #[Test]
    public function it_extracts_a_person_and_assigns_equal_protocol_lines(): void
    {
        Sanctum::actingAs($this->createUser());
        $line = $this->createProtocolLine();
        $equalLine = $this->createEqualProtocolLine($line);

        $response = $this->postJson("/api/v1/protocol-lines/{$line->id}/extract-person")
            ->assertOk()
            ->assertJsonPath('lastname', $line->lastname)
            ->assertJsonPath('firstname', $line->firstname);

        $personId = $response->json('id');

        $this->assertDatabaseHas('person', [
            'id' => $personId,
            'lastname' => $line->lastname,
            'firstname' => $line->firstname,
            'from_base' => false,
        ]);
        $this->assertDatabaseHas('protocol_lines', ['id' => $line->id, 'person_id' => $personId]);
        $this->assertDatabaseHas('protocol_lines', ['id' => $equalLine->id, 'person_id' => $personId]);
    }

    #[Test]
    public function it_assigns_equal_protocol_lines_to_an_existing_person(): void
    {
        Sanctum::actingAs($this->createUser());
        /** @var Person $person */
        $person = Person::factory()->createOne();
        $line = $this->createProtocolLine();
        $equalLine = $this->createEqualProtocolLine($line);

        $this->putJson("/api/v1/protocol-lines/{$line->id}/person", ['personId' => $person->id])
            ->assertNoContent();

        $this->assertDatabaseHas('protocol_lines', ['id' => $line->id, 'person_id' => $person->id]);
        $this->assertDatabaseHas('protocol_lines', ['id' => $equalLine->id, 'person_id' => $person->id]);
    }

    /** @param array<string, mixed> $attributes */
    private function createProtocolLine(array $attributes = []): ProtocolLine
    {
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne();
        /** @var Event $event */
        $event = Event::factory()->createOne(['competition_id' => $competition->id]);
        /** @var Group $group */
        $group = Group::factory()->createOne();
        /** @var Distance $distance */
        $distance = Distance::factory()->createOne(['event_id' => $event->id, 'group_id' => $group->id]);
        /** @var ProtocolLine $line */
        $line = ProtocolLine::factory()->createOne($attributes + [
            'id' => 900001,
            'distance_id' => $distance->id,
            'prepared_line' => 'ivanou-jan-2001',
            'lastname' => 'Іваноў',
            'firstname' => 'Ян',
            'year' => 2001,
        ]);

        return $line;
    }

    private function createEqualProtocolLine(ProtocolLine $line): ProtocolLine
    {
        /** @var ProtocolLine $equalLine */
        $equalLine = ProtocolLine::factory()->createOne([
            'id' => 900002,
            'distance_id' => $line->distance_id,
            'prepared_line' => $line->prepared_line,
            'lastname' => $line->lastname,
            'firstname' => $line->firstname,
            'year' => $line->year,
        ]);

        return $equalLine;
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
    }
}
