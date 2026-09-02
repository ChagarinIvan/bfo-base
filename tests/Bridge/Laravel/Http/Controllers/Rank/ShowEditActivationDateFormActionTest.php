<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Rank;

use App\Bridge\Laravel\Http\Controllers\Rank\ShowEditActivationDateFormAction;
use App\Domain\Auth\User;
use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRankHistory;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Tests\CreatesApplication;
use Tests\TestCase;
use function sprintf;

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
     * @see ShowEditActivationDateFormAction::class
     */
    #[Test]
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
        $distance = Distance::factory()->createOne(['event_id' => $event->id]);
        /** @var ProtocolLine $protocolLine */
        $protocolLine = ProtocolLine::factory()->createOne([
            'distance_id' => (int) $distance->getKey(),
            'person_id' => $person->id,
            'complete_rank' => 'КМС',
            'activate_rank' => '1992-11-10',
        ]);
        $rank = PersonRankHistory::query()->create([
            'person_id' => $person->id,
            'protocol_line_id' => (int) $protocolLine->getKey(),
            'distance_id' => (int) $distance->getKey(),
            'event_id' => $event->id,
            'competition_id' => $competition->id,
            'rank' => Rank::CandidateMaster,
            'change_type' => 'completion',
            'achieved_on' => '1992-11-10',
            'activated_on' => '1992-11-10',
            'started_on' => '1992-11-10',
            'finished_on' => '1994-11-10',
        ]);

        $this->get("/ranks/$protocolLine->id/update-activation")
            ->assertStatus(Response::HTTP_OK)
            ->assertSee(sprintf('<form method="POST" action="http://localhost/ranks/%s/update-activation">', $protocolLine->id), false)
            ->assertSee('<input class="form-control" type="date" id="date" name="date" value="1992-11-10">', false)
        ;
    }
}
