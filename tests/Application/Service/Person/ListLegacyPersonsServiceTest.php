<?php

declare(strict_types=1);

namespace Tests\Application\Service\Person;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Person\LegacySearchPersonDto;
use App\Application\Dto\Person\PersonAssembler;
use App\Application\Service\Person\ListLegacyPersons;
use App\Application\Service\Person\ListLegacyPersonsService;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRepository;
use App\Domain\Shared\Criteria;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ListLegacyPersonsServiceTest extends TestCase
{
    private ListLegacyPersonsService $service;

    private MockObject&PersonRepository $clubs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ListLegacyPersonsService(
            $this->clubs = $this->createMock(PersonRepository::class),
            new PersonAssembler(new AuthAssembler),
        );
    }

    #[Test]
    public function it_gets_list_of_persons(): void
    {
        $persons = Person::factory(count: 2)->make();

        $this->clubs
            ->expects($this->once())
            ->method('byCriteria')
            ->with(Criteria::empty())
            ->willReturn($persons)
        ;

        $result = $this->service->execute(new ListLegacyPersons(new LegacySearchPersonDto()));

        $this->assertCount(2, $result);
    }
}
