<?php

declare(strict_types=1);

namespace Tests\Application\Service\Person;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Person\PersonAssembler;
use App\Application\Dto\Person\PersonSearchDto;
use App\Application\Dto\Person\ViewPersonDto;
use App\Application\Service\Person\ListPersons;
use App\Application\Service\Person\ListPersonsService;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRepository;
use App\Domain\Shared\Criteria;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ListPersonsServiceTest extends TestCase
{
    private ListPersonsService $service;

    private PersonRepository&MockObject $clubs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ListPersonsService(
            $this->clubs = $this->createMock(PersonRepository::class),
            new PersonAssembler(new AuthAssembler),
        );
    }

    /** @test */
    public function it_gets_list_of_persons(): void
    {
        $persons = Person::factory(count: 2)->make();

        $this->clubs
            ->expects($this->once())
            ->method('byCriteria')
            ->with($this->equalTo(Criteria::empty()))
            ->willReturn($persons)
        ;

        $result = $this->service->execute(new ListPersons(new PersonSearchDto()));

        $this->assertContainsOnlyInstancesOf(ViewPersonDto::class, $result);
    }
}
