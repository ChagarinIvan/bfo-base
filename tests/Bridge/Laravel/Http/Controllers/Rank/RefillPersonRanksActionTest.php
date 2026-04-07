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
use App\Domain\Rank\Rank;
use App\Domain\User\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Tests\CreatesApplication;
use Tests\TestCase;

final class RefillPersonRanksActionTest extends TestCase
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
     * @see RefillPersonRanksAction::class
     */
    #[Test]
    public function it_refills_ranks(): void
    {
        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        /** @var Competition $competition1 */
        $competition1 = Competition::factory(['mass' => false])->createOne();
        /** @var Competition $competition2 */
        $competition2 = Competition::factory(['mass' => true])->createOne();
        /** @var Event $event1 */
        $event1 = Event::factory()->createOne(['id' => 101, 'competition_id' => $competition1->id, 'date' => '2021-01-01']);
        /** @var Event $event2 */
        $event2 = Event::factory()->createOne(['id' => 102, 'competition_id' => $competition2->id, 'date' => '2021-01-02']);
        /** @var Person $person */
        $person = Person::factory()->createOne(['id' => 1, 'firstname' => 'John', 'lastname' => 'Doe']);
        Group::factory(state: ['id' => 101, 'name' => 'M21'])->createOne();
        Distance::factory(state: ['id' => 101, 'event_id' => $event1->id, 'group_id' => 101])->createOne();
        Distance::factory(state: ['id' => 102, 'event_id' => $event2->id, 'group_id' => 101])->createOne();
        Rank::factory()->createOne(['person_id' => 1, 'rank' => Rank::SMC_RANK, 'event_id' => 101, 'activated_date' => null]);

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

        ProtocolLine::factory(state: [
            'id' => 103,
            'distance_id' => 102,
            'person_id' => $person->id,
            'complete_rank' => 'КМС',
        ])->createOne();

        $this->post("/ranks/person/$person->id/refill")
            ->assertStatus(Response::HTTP_FOUND)
            ->assertHeader('Location', "http://localhost/ranks/person/$person->id")
        ;

        $this->assertDatabaseCount('ranks', 2);
    }
}
