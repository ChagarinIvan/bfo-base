<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Laravel\Eloquent\Person;

use App\Domain\Club\Club;
use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\Person\Citizenship;
use App\Domain\Person\Person;
use App\Domain\Person\PersonInfo;
use App\Domain\PersonPayment\PersonPayment;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Shared\Criteria;
use App\Infrastructure\Laravel\Eloquent\Person\EloquentPersonRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EloquentPersonRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentPersonRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentPersonRepository();
    }

    #[Test]
    public function it_filters_by_ids(): void
    {
        /** @var Person $p1 */
        $p1 = Person::factory()->createOne(['id' => 1]);
        /** @var Person $p2 */
        $p2 = Person::factory()->createOne(['id' => 2]);
        Person::factory()->createOne(['id' => 3]);

        $result = $this->repository->byCriteria(new Criteria(['ids' => [$p1->id, $p2->id]]));

        $this->assertCount(2, $result);
        $this->assertEqualsCanonicalizing([$p1->id, $p2->id], $result->pluck('id')->all());
    }

    #[Test]
    public function it_filters_by_club_id(): void
    {
        /** @var Club $club */
        $club = Club::factory()->createOne(['id' => 1]);
        Person::factory()->createOne(['id' => 1, 'club_id' => $club->id]);
        Person::factory()->createOne(['id' => 2, 'club_id' => null]);

        $result = $this->repository->byCriteria(new Criteria(['clubId' => $club->id]));

        $this->assertCount(1, $result);
        $this->assertSame($club->id, $result->first()->club_id);
    }

    #[Test]
    public function it_filters_by_birth_year(): void
    {
        Person::factory()->createOne(['id' => 1, 'birthday' => '1990-05-15']);
        Person::factory()->createOne(['id' => 2, 'birthday' => '1995-03-20']);

        $result = $this->repository->byCriteria(new Criteria(['year' => '1990']));

        $this->assertCount(1, $result);
        $this->assertSame(1, $result->first()->id);
    }

    #[Test]
    public function it_filters_by_info(): void
    {
        Person::factory()->createOne([
            'id' => 1,
            'lastname' => 'Ivanov',
            'firstname' => 'Ivan',
            'birthday' => '1990-01-01',
            'citizenship' => Citizenship::BELARUS,
        ]);
        Person::factory()->createOne([
            'id' => 2,
            'lastname' => 'Petrov',
            'firstname' => 'Petr',
            'birthday' => '1985-06-15',
            'citizenship' => Citizenship::OTHER,
        ]);

        $info = new PersonInfo(
            firstname: 'Ivan',
            lastname: 'Ivanov',
            birthday: Carbon::parse('1990-01-01'),
            citizenship: Citizenship::BELARUS,
            clubId: null,
        );

        $result = $this->repository->byCriteria(new Criteria(['info' => $info]));

        $this->assertCount(1, $result);
        $this->assertSame(1, $result->first()->id);
    }

    #[Test]
    public function it_filters_persons_without_lines_and_payments(): void
    {
        /** @var Person $personA */
        $personA = Person::factory()->createOne(['id' => 1]);
        /** @var Person $personB */
        $personB = Person::factory()->createOne(['id' => 2]);
        /** @var Person $personC */
        $personC = Person::factory()->createOne(['id' => 3]);

        // personB has a protocol line
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne();
        /** @var Event $event */
        $event = Event::factory()->createOne(['competition_id' => $competition->id]);
        /** @var Group $group */
        $group = Group::factory()->createOne(['id' => 201]);
        /** @var Distance $distance */
        $distance = Distance::factory()->createOne(['event_id' => $event->id, 'group_id' => $group->id]);
        ProtocolLine::factory()->createOne(['person_id' => $personB->id, 'distance_id' => $distance->id]);

        // personC has a payment
        PersonPayment::factory()->createOne(['person_id' => $personC->id, 'year' => 2024]);

        $result = $this->repository->byCriteria(new Criteria(['withoutLinesAndPayments' => true]));

        $this->assertCount(1, $result);
        $this->assertSame($personA->id, $result->first()->id);
    }

    #[Test]
    public function it_returns_one_by_criteria(): void
    {
        Person::factory()->createOne(['id' => 1, 'lastname' => 'Sidorov']);
        Person::factory()->createOne(['id' => 2, 'lastname' => 'Kozlov']);

        $result = $this->repository->oneByCriteria(new Criteria(['ids' => [1]]));

        $this->assertInstanceOf(Person::class, $result);
        $this->assertSame(1, $result->id);
    }

    #[Test]
    public function it_finds_active_person_by_id(): void
    {
        Person::factory()->createOne(['id' => 1, 'active' => true]);

        $result = $this->repository->byId(1);

        $this->assertInstanceOf(Person::class, $result);
        $this->assertSame(1, $result->id);
    }

    #[Test]
    public function it_does_not_return_inactive_person_by_id(): void
    {
        Person::factory()->createOne(['id' => 1, 'active' => false]);

        $result = $this->repository->byId(1);

        $this->assertNotInstanceOf(Person::class, $result);
    }

    #[Test]
    public function it_orders_by_lastname(): void
    {
        Person::factory()->createOne(['id' => 1, 'lastname' => 'Zubov']);
        Person::factory()->createOne(['id' => 2, 'lastname' => 'Abramov']);

        $result = $this->repository->byCriteria(Criteria::empty());

        $this->assertSame('Abramov', $result->first()->lastname);
    }
}
