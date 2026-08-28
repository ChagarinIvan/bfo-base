<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Laravel\Eloquent\Rank;

use App\Domain\Competition\Competition;
use App\Domain\Event\Event;
use App\Domain\Person\Person;
use App\Domain\Rank\Rank;
use App\Domain\Shared\Criteria;
use App\Infrastructure\Laravel\Eloquent\Rank\EloquentRankRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EloquentRankRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentRankRepository $repository;

    private Competition $sharedCompetition;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentRankRepository();
        /** @var Competition $sharedCompetition */
        $sharedCompetition = Competition::factory()->createOne(['id' => 1]);
        $this->sharedCompetition = $sharedCompetition;
    }

    #[Test]
    public function it_filters_ranks_by_person_id(): void
    {
        Person::factory()->createOne(['id' => 1]);
        Person::factory()->createOne(['id' => 2]);

        $this->createRankForPerson(personId: 1, eventDate: '2024-01-01', finishDate: '2026-01-01');
        $this->createRankForPerson(personId: 1, eventDate: '2024-02-01', finishDate: '2026-02-01');
        $this->createRankForPerson(personId: 2, eventDate: '2024-03-01', finishDate: '2026-03-01');

        $result = $this->repository->byCriteria(new Criteria(['person_id' => 1]));

        $this->assertCount(2, $result);
        $this->assertSame([1, 1], $result->pluck('person_id')->all());
    }

    #[Test]
    public function it_filters_by_activated_flag(): void
    {
        Person::factory()->createOne(['id' => 1]);

        $this->createRankForPerson(personId: 1, eventDate: '2024-01-01', finishDate: '2026-01-01', activatedDate: '2024-06-01');
        $this->createRankForPerson(personId: 1, eventDate: '2024-02-01', finishDate: '2026-02-01');

        $activated = $this->repository->byCriteria(new Criteria(['person_id' => 1, 'activated' => true]));
        $notActivated = $this->repository->byCriteria(new Criteria(['person_id' => 1, 'activated' => false]));

        $this->assertCount(1, $activated);
        $this->assertNotNull($activated->first()->activated_date);
        $this->assertCount(1, $notActivated);
        $this->assertNull($notActivated->first()->activated_date);
    }

    #[Test]
    public function it_orders_by_finish_date_desc_by_default(): void
    {
        Person::factory()->createOne(['id' => 1]);

        $this->createRankForPerson(personId: 1, eventDate: '2024-01-01', finishDate: '2026-01-01');
        $this->createRankForPerson(personId: 1, eventDate: '2024-02-01', finishDate: '2027-01-01');

        $result = $this->repository->byCriteria(new Criteria(['person_id' => 1]));

        $this->assertSame('2027-01-01', $result->first()->finish_date->toDateString());
    }

    #[Test]
    public function it_filters_by_date_range(): void
    {
        Person::factory()->createOne(['id' => 1]);

        $this->createRankForPerson(personId: 1, eventDate: '2024-01-01', finishDate: '2025-06-01');
        $this->createRankForPerson(personId: 1, eventDate: '2025-07-01', finishDate: '2027-07-01');

        // date=2025-01-01 matches rank1 (start<=2025-01-01<=finish) but not rank2
        $result = $this->repository->byCriteria(new Criteria(['date' => '2025-01-01']));

        $this->assertCount(1, $result);
        $this->assertSame('2025-06-01', $result->first()->finish_date->toDateString());
    }

    #[Test]
    public function it_filters_by_finish_date_to(): void
    {
        Person::factory()->createOne(['id' => 1]);

        $this->createRankForPerson(personId: 1, eventDate: '2024-01-01', finishDate: '2025-01-01');
        $this->createRankForPerson(personId: 1, eventDate: '2024-01-01', finishDate: '2027-01-01');

        $result = $this->repository->byCriteria(new Criteria(['person_id' => 1, 'finish_date_to' => '2025-06-01']));

        $this->assertCount(1, $result);
        $this->assertSame('2025-01-01', $result->first()->finish_date->toDateString());
    }

    #[Test]
    public function it_filters_by_start_date_less(): void
    {
        Person::factory()->createOne(['id' => 1]);

        $this->createRankForPerson(personId: 1, eventDate: '2023-01-01', finishDate: '2025-01-01');
        $this->createRankForPerson(personId: 1, eventDate: '2025-07-01', finishDate: '2027-07-01');

        $result = $this->repository->byCriteria(new Criteria(['person_id' => 1, 'startDateLess' => '2024-01-01']));

        $this->assertCount(1, $result);
        $this->assertSame('2023-01-01', $result->first()->start_date->toDateString());
    }

    #[Test]
    public function it_filters_by_activation_date_from(): void
    {
        Person::factory()->createOne(['id' => 1]);

        $this->createRankForPerson(personId: 1, eventDate: '2024-01-01', finishDate: '2026-01-01', activatedDate: '2024-03-01');
        $this->createRankForPerson(personId: 1, eventDate: '2024-01-01', finishDate: '2026-01-01', activatedDate: '2024-08-01');

        $result = $this->repository->byCriteria(new Criteria(['person_id' => 1, 'activation_date_from' => '2024-06-01']));

        $this->assertCount(1, $result);
        $this->assertSame('2024-08-01', $result->first()->activated_date->toDateString());
    }

    #[Test]
    public function it_filters_by_rank(): void
    {
        Person::factory()->createOne(['id' => 1]);

        /** @var Event $event */
        $event = Event::factory()->createOne(['competition_id' => $this->sharedCompetition->id, 'date' => '2024-01-01']);
        Rank::factory()->createOne(['person_id' => 1, 'event_id' => $event->id, 'rank' => Rank::SMC_RANK, 'start_date' => '2024-01-01', 'finish_date' => '2026-01-01']);
        Rank::factory()->createOne(['person_id' => 1, 'event_id' => $event->id, 'rank' => Rank::FIRST_RANK, 'start_date' => '2024-01-01', 'finish_date' => '2026-01-01']);

        $result = $this->repository->byCriteria(new Criteria(['person_id' => 1, 'rank' => Rank::FIRST_RANK]));

        $this->assertCount(1, $result);
        $this->assertSame(Rank::FIRST_RANK, $result->first()->rank);
    }

    #[Test]
    public function it_filters_by_rank_in(): void
    {
        Person::factory()->createOne(['id' => 1]);

        /** @var Event $event */
        $event = Event::factory()->createOne(['competition_id' => $this->sharedCompetition->id, 'date' => '2024-01-01']);
        Rank::factory()->createOne(['person_id' => 1, 'event_id' => $event->id, 'rank' => Rank::SMC_RANK, 'start_date' => '2024-01-01', 'finish_date' => '2026-01-01']);
        Rank::factory()->createOne(['person_id' => 1, 'event_id' => $event->id, 'rank' => Rank::FIRST_RANK, 'start_date' => '2024-01-01', 'finish_date' => '2026-01-01']);
        Rank::factory()->createOne(['person_id' => 1, 'event_id' => $event->id, 'rank' => Rank::SM_RANK, 'start_date' => '2024-01-01', 'finish_date' => '2026-01-01']);

        $result = $this->repository->byCriteria(new Criteria(['person_id' => 1, 'rank_in' => [Rank::SMC_RANK, Rank::FIRST_RANK]]));

        $this->assertCount(2, $result);
        $this->assertContains(Rank::SMC_RANK, $result->pluck('rank')->all());
        $this->assertContains(Rank::FIRST_RANK, $result->pluck('rank')->all());
    }

    #[Test]
    public function it_filters_by_event_id(): void
    {
        Person::factory()->createOne(['id' => 1]);

        /** @var Event $event1 */
        $event1 = Event::factory()->createOne(['competition_id' => $this->sharedCompetition->id, 'date' => '2024-01-01']);
        /** @var Event $event2 */
        $event2 = Event::factory()->createOne(['competition_id' => $this->sharedCompetition->id, 'date' => '2024-02-01']);
        Rank::factory()->createOne(['person_id' => 1, 'event_id' => $event1->id, 'rank' => Rank::SMC_RANK, 'start_date' => '2024-01-01', 'finish_date' => '2026-01-01']);
        Rank::factory()->createOne(['person_id' => 1, 'event_id' => $event2->id, 'rank' => Rank::SMC_RANK, 'start_date' => '2024-02-01', 'finish_date' => '2026-02-01']);

        $result = $this->repository->byCriteria(new Criteria(['event_id' => $event1->id]));

        $this->assertCount(1, $result);
        $this->assertSame($event1->id, $result->first()->event_id);
    }

    #[Test]
    public function it_applies_custom_sorting(): void
    {
        Person::factory()->createOne(['id' => 1]);

        $this->createRankForPerson(personId: 1, eventDate: '2024-01-01', finishDate: '2026-01-01');
        $this->createRankForPerson(personId: 1, eventDate: '2024-02-01', finishDate: '2027-01-01');

        $result = $this->repository->byCriteria(new Criteria(['person_id' => 1], ['finish_date' => 'asc']));

        $this->assertSame('2026-01-01', $result->first()->finish_date->toDateString());
    }

    #[Test]
    public function it_returns_one_by_criteria(): void
    {
        Person::factory()->createOne(['id' => 1]);

        $this->createRankForPerson(personId: 1, eventDate: '2024-01-01', finishDate: '2027-01-01');

        $rank = $this->repository->oneByCriteria(new Criteria(['person_id' => 1]));

        $this->assertInstanceOf(Rank::class, $rank);
        $this->assertSame(1, $rank->person_id);
    }

    #[Test]
    public function it_returns_null_for_one_by_criteria_when_not_found(): void
    {
        $result = $this->repository->oneByCriteria(new Criteria(['person_id' => 999]));

        $this->assertNotInstanceOf(Rank::class, $result);
    }

    #[Test]
    public function it_deletes_by_criteria(): void
    {
        Person::factory()->createOne(['id' => 1]);
        Person::factory()->createOne(['id' => 2]);

        $this->createRankForPerson(personId: 1, eventDate: '2024-01-01', finishDate: '2026-01-01');
        $this->createRankForPerson(personId: 2, eventDate: '2024-01-01', finishDate: '2026-01-01');

        $this->repository->deleteByCriteria(new Criteria(['person_id' => 1]));

        $this->assertDatabaseCount('ranks', 1);
        $this->assertDatabaseMissing('ranks', ['person_id' => 1]);
        $this->assertDatabaseHas('ranks', ['person_id' => 2]);
    }

    #[Test]
    public function it_finds_by_id(): void
    {
        Person::factory()->createOne(['id' => 1]);

        /** @var Event $event */
        $event = Event::factory()->createOne(['competition_id' => $this->sharedCompetition->id, 'date' => '2024-01-01']);
        /** @var Rank $rank */
        $rank = Rank::factory()->createOne(['person_id' => 1, 'event_id' => $event->id, 'rank' => Rank::SMC_RANK, 'start_date' => '2024-01-01', 'finish_date' => '2026-01-01']);

        $found = $this->repository->byId($rank->id);

        $this->assertInstanceOf(Rank::class, $found);
        $this->assertSame($rank->id, $found->id);
        $this->assertNotInstanceOf(Rank::class, $this->repository->byId(999999));
    }

    private function createRankForPerson(
        int $personId,
        string $eventDate,
        string $finishDate,
        ?string $activatedDate = null,
    ): void {
        /** @var Event $event */
        $event = Event::factory()->createOne(['competition_id' => $this->sharedCompetition->id, 'date' => $eventDate]);

        Rank::factory()->createOne([
            'person_id' => $personId,
            'event_id' => $event->id,
            'rank' => Rank::SMC_RANK,
            'start_date' => $eventDate,
            'finish_date' => $finishDate,
            'activated_date' => $activatedDate,
        ]);
    }
}
