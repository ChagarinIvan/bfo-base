<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Laravel\Eloquent\Person;

use App\Domain\Person\Citizenship;
use App\Domain\Person\Person;
use App\Domain\Person\PersonInfo;
use App\Domain\PersonPayment\PersonPayment;
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
    public function it_filters_paginated_persons_by_ids(): void
    {
        /** @var Person $person */
        $person = Person::factory()->createOne(['id' => 1]);
        Person::factory()->createOne(['id' => 2]);

        $result = $this->repository
            ->paginate(new Criteria(['ids' => [$person->id]]))
            ->setPerPage(1000)
            ->items()
        ;

        $this->assertCount(1, $result);
        $this->assertSame($person->id, $result[0]->id);
    }

    #[Test]
    public function it_filters_by_person_info_without_loading_payments(): void
    {
        /** @var Person $person */
        $person = Person::factory()->createOne([
            'id' => 1,
            'lastname' => 'Ivanov',
            'firstname' => 'Ivan',
            'birthday' => '1990-01-01',
            'citizenship' => Citizenship::BELARUS,
        ]);
        PersonPayment::factory()->createOne(['person_id' => $person->id, 'year' => 2024]);
        Person::factory()->createOne([
            'id' => 2,
            'lastname' => 'Petrov',
            'firstname' => 'Petr',
            'birthday' => '1985-06-15',
            'citizenship' => Citizenship::OTHER,
        ]);

        $result = $this->repository->byCriteria(new Criteria(['info' => $this->personInfo()]));

        $this->assertCount(1, $result);
        $this->assertSame($person->id, $result->first()->id);
        $this->assertSame([], $result->first()->getRelations());
    }

    #[Test]
    public function it_filters_by_person_name(): void
    {
        Person::factory()->createOne(['id' => 1, 'lastname' => 'Ivanov', 'firstname' => 'Ivan']);
        Person::factory()->createOne(['id' => 2, 'lastname' => 'Ivanov', 'firstname' => 'Petr']);

        $result = $this->repository->byCriteria(new Criteria([
            'lastname' => 'Ivanov',
            'firstname' => 'Ivan',
        ]));

        $this->assertCount(1, $result);
        $this->assertSame(1, $result->first()->id);
    }

    #[Test]
    public function it_returns_one_person_by_info_criteria(): void
    {
        Person::factory()->createOne(['id' => 1, 'lastname' => 'Sidorov']);
        /** @var Person $expected */
        $expected = Person::factory()->createOne([
            'id' => 2,
            'lastname' => 'Ivanov',
            'firstname' => 'Ivan',
            'birthday' => '1990-01-01',
            'citizenship' => Citizenship::BELARUS,
        ]);

        $result = $this->repository->oneByCriteria(new Criteria(['info' => $this->personInfo()]));

        $this->assertSame($expected->id, $result?->id);
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

    private function personInfo(): PersonInfo
    {
        return new PersonInfo(
            firstname: 'Ivan',
            lastname: 'Ivanov',
            birthday: Carbon::parse('1990-01-01'),
            citizenship: Citizenship::BELARUS,
            clubId: null,
        );
    }
}
