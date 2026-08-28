<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Rank;

use App\Bridge\Laravel\Http\Controllers\Rank\ShowRanksListAction;
use App\Domain\Competition\Competition;
use App\Domain\Event\Event;
use App\Domain\Person\Person;
use App\Domain\Rank\Rank;
use App\Models\Year;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ShowRanksListActionTest extends TestCase
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
     * @see ShowRanksListAction::class
     */
    #[Test]
    public function it_shows_empty_rank_list_for_rank_type(): void
    {
        $this->get('/ranks/list/' . Rank::FIRST_RANK)
            ->assertStatus(Response::HTTP_OK);
    }

    #[Test]
    public function it_renders_rank_list_with_seeded_ranks(): void
    {
        $year = Year::actualYear()->value;

        /** @var Competition $competition */
        $competition = Competition::factory()->createOne();
        /** @var Event $event */
        $event = Event::factory()->createOne([
            'competition_id' => $competition->id,
            'date' => ($year - 1) . '-06-01',
        ]);
        /** @var Person $person */
        $person = Person::factory()->createOne(['id' => 1]);

        Rank::factory()->createOne([
            'person_id' => $person->id,
            'event_id' => $event->id,
            'rank' => Rank::FIRST_RANK,
            'start_date' => ($year - 1) . '-01-01',
            'finish_date' => ($year + 1) . '-01-01',
            'activated_date' => ($year - 1) . '-01-01',
        ]);

        $this->get('/ranks/list/' . Rank::FIRST_RANK)
            ->assertStatus(Response::HTTP_OK);
    }
}
