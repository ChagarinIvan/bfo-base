<?php

declare(strict_types=1);

namespace Tests\Application\Service\Person;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Person\PersonAssembler;
use App\Application\Dto\Person\ViewPersonDto;
use App\Application\Service\Person\Exception\PersonNotFound;
use App\Application\Service\Person\ViewPerson;
use App\Application\Service\Person\ViewPersonService;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ViewPersonServiceTest extends TestCase
{
    private PersonRepository&MockObject $persons;

    private ViewPersonService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->persons = $this->createMock(PersonRepository::class);
        $this->service = new ViewPersonService($this->persons, new PersonAssembler(new AuthAssembler));
    }

    /** @test */
    public function it_fails_when_person_does_not_exist(): void
    {
        $this->expectException(PersonNotFound::class);

        $this->persons
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo(1))
            ->willReturn(null)
        ;

        $this->service->execute(new ViewPerson('1'));
    }

    /** @test */
    public function it_views_person(): void
    {
        /** @var Person $person */
        $person = Person::factory()->makeOne();

        $this->persons
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo($person->id))
            ->willReturn($person)
        ;

        $dto = $this->service->execute(new ViewPerson((string) $person->id));

        $this->assertInstanceOf(ViewPersonDto::class, $dto);
        $this->assertEquals($person->lastname, $dto->lastname);
    }
}
