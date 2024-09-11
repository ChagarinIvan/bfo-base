<?php

namespace Tests\Bridge\Laravel\Http\Controllers\Rank;

use App\Bridge\Laravel\Http\Controllers\Rank\ShowEditActivationDateFormAction;
use App\Domain\Competition\Competition;
use App\Domain\Event\Event;
use App\Domain\Person\Person;
use App\Domain\Rank\Rank;
use App\Domain\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Contracts\Auth\Authenticatable;
use Tests\CreatesApplication;
use Tests\TestCase;
use Illuminate\Http\Response;

final class ShowEditActivationDateFormActionTest extends TestCase
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
     * @see ShowEditActivationDateFormAction::class
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

        $this->get("/ranks/$rank->id/update-activation")
            ->assertStatus(Response::HTTP_OK)
            ->assertSee(sprintf('<form method="POST" action="http://localhost/ranks/%s/update-activation">', $rank->id), false)
            ->assertSee('<input class="form-control" type="date" id="date" name="date" value="1992-11-10">', false)
        ;
    }
}
