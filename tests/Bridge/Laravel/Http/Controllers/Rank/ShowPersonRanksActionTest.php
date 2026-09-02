<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Rank;

use App\Bridge\Laravel\Http\Controllers\Rank\ShowPersonRanksAction;
use App\Domain\Auth\User;
use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRankHistory;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use App\Models\Year;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Tests\CreatesApplication;
use Tests\TestCase;

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
     * @see ShowPersonRanksAction::class
     */
    #[Test]
    public function it_shows_person_rank(): void
    {
        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $year = Year::actualYear();
        $year->previous();
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne();
        /** @var Event $event */
        $event = Event::factory()->createOne(['competition_id' => $competition->id, 'date' => '2025-01-01']);
        /** @var Person $person */
        $person = Person::factory()->createOne(['id' => 1, 'firstname' => 'John', 'lastname' => 'Doe']);
        Group::factory(state: ['id' => 101, 'name' => 'M21'])->createOne();
        Distance::factory(state: ['id' => 101, 'event_id' => $event->id, 'group_id' => 101])->createOne();
        ProtocolLine::factory(state: [
            'id' => 101,
            'distance_id' => 101,
            'person_id' => $person->id,
            'complete_rank' => 'I',
            'activate_rank' => "{$year->previous()->value}-01-01",
        ])->createOne();

        $person->update([
            'current_rank' => Rank::FirstRank,
            'current_rank_finished_on' => ($year->value + 1) . '-01-01',
        ]);
        PersonRankHistory::create([
            'person_id' => $person->id,
            'protocol_line_id' => 101,
            'distance_id' => 101,
            'event_id' => $event->id,
            'competition_id' => $competition->id,
            'rank' => Rank::CandidateMaster,
            'change_type' => 'completion',
            'achieved_on' => '2022-01-01',
            'activated_on' => '2022-01-01',
            'started_on' => '2022-01-01',
            'finished_on' => '2023-01-01',
        ]);
        ProtocolLine::factory(state: [
            'id' => 102,
            'distance_id' => 101,
            'person_id' => $person->id,
            'complete_rank' => 'II',
            'activate_rank' => "{$year->previous()->value}-01-01",
        ])->createOne();
        PersonRankHistory::create([
            'person_id' => $person->id,
            'protocol_line_id' => 102,
            'distance_id' => 101,
            'event_id' => $event->id,
            'competition_id' => $competition->id,
            'rank' => Rank::FirstRank,
            'change_type' => 'promotion',
            'achieved_on' => ($year->previous()->value) . '-01-01',
            'activated_on' => ($year->previous()->value) . '-01-01',
            'started_on' => ($year->previous()->value) . '-01-01',
            'finished_on' => ($year->value + 1) . '-01-01',
        ]);

        $next = $year->value + 1;
        $this->get("/ranks/person/$person->id")
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<div class="row"><h4>Doe John</h4></div>', false)
            ->assertSee("<h4>I до $next-01-01</h4>", false)
            ->assertSee('<td>КМС</td>', false)
            ->assertSee("<td>{$year->previous()->previous()->previous()->value}-01-01</td>", false)
            ->assertSee("<td>{$year->previous()->value}-01-01</td>", false)
            ->assertSee('<td>I</td>', false)
            ->assertSee("<td>{$year->previous()->value}-01-01</td>", false)
            ->assertSee("<td>$next-01-01</td>", false)
        ;
    }
}
