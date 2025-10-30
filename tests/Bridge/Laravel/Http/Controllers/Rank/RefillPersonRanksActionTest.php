<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Rank;

use App\Bridge\Laravel\Http\Controllers\Rank\RefillPersonRanksAction;
use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\Person\Person;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\User\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

class RefillPersonRanksActionTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createApplication();
        RefreshDatabaseState::$migrated = false;
    }

    /**
     * @test
     * @see RefillPersonRanksAction::class
     */
    public function it_refills_ranks(): void
    {
        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        /** @var Competition $competition */
        $competition = Competition::factory()->createOne();
        /** @var Event $event */
        $event = Event::factory()->createOne(['competition_id' => $competition->id, 'date' => '2021-01-01']);
        /** @var Person $person */
        $person = Person::factory()->createOne(['id' => 1, 'firstname' => 'John', 'lastname' => 'Doe']);
        Group::factory(state: ['id' => 101, 'name' => 'M21'])->createOne();
        Distance::factory(state: ['id' => 101, 'event_id' => $event->id, 'group_id' => 101])->createOne();
        ProtocolLine::factory(state: [
            'id' => 101,
            'distance_id' => 101,
            'person_id' => $person->id,
            'complete_rank' => 'I',
        ])->createOne();
        ProtocolLine::factory(state: [
            'id' => 102,
            'distance_id' => 101,
            'person_id' => $person->id,
            'complete_rank' => 'II',
        ])->createOne();

        $this->post("/ranks/person/$person->id/refill")
            ->assertStatus(Response::HTTP_FOUND)
            ->assertHeader('Location', "http://localhost/ranks/person/$person->id");
        ;
    }
}
