<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Rank;

use App\Bridge\Laravel\Http\Controllers\Rank\ShowPersonRanksAction;
use App\Domain\Competition\Competition;
use App\Domain\Event\Event;
use App\Domain\Person\Person;
use App\Domain\Rank\Rank;
use App\Domain\User\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
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
     * @test
     * @see ShowPersonRanksAction::class
     */
    public function it_shows_edit_activation_date_form(): void
    {
        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        /** @var Competition $competition */
        $competition = Competition::factory()->createOne();
        /** @var Event $event */
        $event = Event::factory()->createOne(['competition_id' => $competition->id]);
        /** @var Person $person */
        $person = Person::factory()->createOne();
        /** @var Rank $rank */
        $rank = Rank::factory()->createOne(['person_id' => $person->id, 'event_id' => $event->id, 'start_date' => '1992-11-10']);

        $this->get("/ranks/person/{$person->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertSee(sprintf('<form method="POST" action="http://localhost/ranks/%s/update-activation">', $rank->id), false)
            ->assertSee('<input class="form-control" type="date" id="date" name="date" value="1992-11-10">', false)
        ;
    }
}
