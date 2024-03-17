<?php

declare(strict_types=1);

namespace Tests\Application\Service\Person;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\PersonAssembler;
use App\Application\Dto\Person\PersonDto;
use App\Application\Dto\Person\PersonInfoDto;
use App\Application\Service\Person\AddPerson;
use App\Application\Service\Person\AddPersonService;
use App\Domain\Person\Factory\PersonFactory;
use App\Domain\Person\Factory\PersonInput;
use App\Domain\Person\Person;
use App\Domain\Person\PersonInfo;
use App\Domain\Person\PersonRepository;
use Carbon\Carbon;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class AddPersonServiceTest extends TestCase
{
    private AddPersonService $service;

    private PersonFactory&MockObject $factory;

    private PersonRepository&MockObject $persons;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AddPersonService(
            $this->factory = $this->createMock(PersonFactory::class),
            $this->persons = $this->createMock(PersonRepository::class),
            new PersonAssembler(new AuthAssembler),
        );
    }

    /** @test */
    public function it_creates_person(): void
    {
        $info = new PersonInfo(
            firstname: 'test firstname',
            lastname: 'test lastname',
            birthday: Carbon::parse('1988-01-02'),
            clubId: 1,
        );

        $input = new PersonInput(
            info: $info,
            fromBase: false,
            userId: 1,
        );

        /** @var Person $person */
        $person = Person::factory()->makeOne();

        $this->factory
            ->expects($this->once())
            ->method('create')
            ->with($this->equalTo($input))
            ->willReturn($person)
        ;

        $this->persons
            ->expects($this->once())
            ->method('add')
            ->with($this->identicalTo($person))
        ;

        $infoDto = new PersonInfoDto();
        $infoDto->firstname = 'test firstname';
        $infoDto->lastname = 'test lastname';
        $infoDto->birthday = '1988-01-02';
        $infoDto->clubId = '1';

        $dto = new PersonDto();
        $dto->info = $infoDto;

        $command = new AddPerson($dto, new UserId(1));
        $personDto = $this->service->execute($command);

        $this->assertEquals($person->id, $personDto->id);
    }
}
