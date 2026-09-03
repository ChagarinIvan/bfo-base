<?php

declare(strict_types=1);

namespace Tests\Application\Service\Person;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Person\PersonAssembler;
use App\Application\Dto\Person\SearchPersonDto;
use App\Application\Service\Person\ListPersons;
use App\Application\Service\Person\ListPersonsService;
use App\Domain\Auth\Impression;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRank;
use App\Domain\Person\PersonRepository;
use App\Domain\Rank\Rank;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use Carbon\Carbon;
use Pagerfanta\Adapter\ArrayAdapter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ListPersonsServiceTest extends TestCase
{
    private ListPersonsService $service;

    private MockObject&PersonRepository $persons;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ListPersonsService(
            $this->persons = $this->createMock(PersonRepository::class),
            new PersonAssembler(new AuthAssembler),
        );
    }

    #[Test]
    public function it_returns_a_compact_paginated_person_slice_for_a_club(): void
    {
        $person = $this->createStub(Person::class);
        $person->method('__get')->willReturnMap([
            ['id', 42],
            ['lastname', 'Ivanov'],
            ['firstname', 'Ivan'],
            ['birthday', new Carbon('2001-06-04')],
            ['created', $this->impressionValue()],
            ['updated', $this->impressionValue()],
        ]);
        $person->method('currentRank')->willReturn(new PersonRank(Rank::WithoutRank, null, null, null));

        $this->persons
            ->expects($this->once())
            ->method('paginate')
            ->with(new Criteria(['clubId' => 7]))
            ->willReturn(new Slice(new ArrayAdapter([$person])))
        ;
        $result = $this->service->execute(
            new ListPersons(new SearchPersonDto(clubId: 7)),
        );

        $items = $result->items();
        $this->assertSame('42', $items[0]->id);
        $this->assertSame('2001-06-04', $items[0]->birthday);
        $this->assertObjectNotHasProperty('citizenship', $items[0]);
        $this->assertNull($items[0]->clubId);
    }

    #[Test]
    public function it_passes_the_normalized_name_filter_to_the_repository(): void
    {
        $this->persons
            ->expects($this->once())
            ->method('paginate')
            ->with(new Criteria(['name' => 'Ivan']))
            ->willReturn(new Slice(new ArrayAdapter([])))
        ;

        $search = (new SearchPersonDto)->fromArray(
            SearchPersonDto::normaliseRequestData(['name' => '  Ivan  ']),
        );

        $result = $this->service->execute(new ListPersons($search));

        $this->assertCount(0, $result);
    }

    #[Test]
    public function it_passes_person_ids_to_the_repository(): void
    {
        $this->persons
            ->expects($this->once())
            ->method('paginate')
            ->with(new Criteria(['ids' => [7, 8]]))
            ->willReturn(new Slice(new ArrayAdapter([])))
        ;

        $result = $this->service->execute(
            new ListPersons(new SearchPersonDto(ids: [7, 8])),
        );

        $this->assertCount(0, $result);
    }

    #[Test]
    public function it_leaves_the_club_filter_optional_for_the_general_v1_list(): void
    {
        $this->persons
            ->expects($this->once())
            ->method('paginate')
            ->with(Criteria::empty())
            ->willReturn(new Slice(new ArrayAdapter([])))
        ;

        $result = $this->service->execute(new ListPersons(new SearchPersonDto()));

        $this->assertCount(0, $result);
    }

    #[Test]
    public function it_passes_the_without_lines_and_payments_filter_to_the_repository(): void
    {
        $this->persons
            ->expects($this->once())
            ->method('paginate')
            ->with(new Criteria(['withoutLinesAndPayments' => true]))
            ->willReturn(new Slice(new ArrayAdapter([])))
        ;

        $result = $this->service->execute(
            new ListPersons(new SearchPersonDto(withoutLinesAndPayments: true)),
        );

        $this->assertCount(0, $result);
    }

    private function impressionValue(): Impression
    {
        return new Impression(new Carbon('2026-01-01'), 1);
    }
}
