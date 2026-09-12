<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Event;

use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
    public function it_unites_events_without_a_protocol_file(): void
    {
        Sanctum::actingAs($this->createUser());
        $competition = Competition::factory()->createOne();
        /** @var Event $firstEvent */
        $firstEvent = Event::factory()->createOne([
            'competition_id' => $competition->getKey(),
            'date' => '2026-05-10',
        ]);
        /** @var Event $secondEvent */
        $secondEvent = Event::factory()->createOne([
            'competition_id' => $competition->getKey(),
            'date' => '2026-05-11',
        ]);
        /** @var Group $group */
        $group = Group::factory()->createOne();
        /** @var Distance $firstDistance */
        $firstDistance = Distance::factory()->createOne([
            'event_id' => $firstEvent->getKey(),
            'group_id' => $group->getKey(),
        ]);
        /** @var Distance $secondDistance */
        $secondDistance = Distance::factory()->createOne([
            'event_id' => $secondEvent->getKey(),
            'group_id' => $group->getKey(),
        ]);
        ProtocolLine::factory()->createOne([
            'distance_id' => $firstDistance->getKey(),
            'time' => '00:10:00',
        ]);
        ProtocolLine::factory()->createOne([
            'distance_id' => $secondDistance->getKey(),
            'time' => '00:20:00',
        ]);

        $this->postJson("/api/v1/competitions/{$competition->getKey()}/events/unite", [
            'eventIds' => [$firstEvent->getKey(), $secondEvent->getKey()],
        ])
            ->assertCreated()
            ->assertJsonPath('competitionId', (string) $competition->getKey())
        ;

        $this->assertDatabaseHas('events', [
            'name' => $firstEvent->name . ' + ' . $secondEvent->name,
            'file' => '',
        ]);
    }

    #[Test]
    public function it_requires_one_valid_replacement_protocol_source(): void
    {
        Sanctum::actingAs($this->createUser());
        $competition = Competition::factory()->createOne();
        $event = Event::factory()->createOne(['competition_id' => $competition->getKey()]);
        $data = [
            'name' => 'Updated stage',
            'description' => 'Updated description',
            'date' => '2026-05-11',
            'protocol' => UploadedFile::fake()->create('protocol.html', 10, 'text/html'),
            'url' => 'https://obelarus.net/protocol/1',
        ];

        $this->put("/api/v1/events/{$event->getKey()}", $data)
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'protocol'])
            ->assertJsonFragment(['field' => 'url'])
        ;
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
