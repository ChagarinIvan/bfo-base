<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Rank;

use App\Bridge\Laravel\Http\Controllers\Rank\ShowPersonRanksAction;
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
use Tests\CreatesApplication;
use Tests\TestCase;
use function sprintf;

final class ShowPersonRanksActionTest extends TestCase
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
     * @see ShowPersonRanksAction::class
     */
    public function it_shows_person_rank(): void
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
        Rank::factory()->createOne([
            'person_id' => $person->id,
            'event_id' => $event->id,
            'start_date' => '2020-01-01',
            'finish_date' => '2022-01-01',
            'activated_date' => '2020-01-01',
            'rank' => Rank::SMC_RANK,
        ]);
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

        $this->get("/ranks/person/$person->id")
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<div class="row"><h4>Doe John</h4></div>', false)
            ->assertSee('<h4>I до 2023-01-01</h4>', false)
            ->assertSee('<td>КМС</td>', false)
            ->assertSee('<td>2020-01-01</td>', false)
            ->assertSee('<td>2022-01-01</td>', false)
            ->assertSee('<td>I</td>', false)
            ->assertSee('<td>2022-01-02</td>', false)
            ->assertSee('<td>2023-01-01</td>', false)
        ;
    }
}
