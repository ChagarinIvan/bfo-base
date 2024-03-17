<?php

declare(strict_types=1);

namespace Tests\Application\Service\Person;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\PersonAssembler;
use App\Application\Dto\Person\PersonInfoDto;
use App\Application\Service\Person\Exception\PersonNotFound;
use App\Application\Service\Person\UpdatePersonInfo;
use App\Application\Service\Person\UpdatePersonInfoService;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRepository;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\FrozenClock;
use Carbon\Carbon;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class UpdatePersonInfoServiceTest extends TestCase
{
    private UpdatePersonInfoService $service;

    private PersonRepository&MockObject $persons;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new UpdatePersonInfoService(
            $this->persons = $this->createMock(PersonRepository::class),
            new FrozenClock(Carbon::parse('2023-04-01')),
            new PersonAssembler(new AuthAssembler),
            new DummyTransactional(),
        );
    }

    /** @test */
    public function it_fails_when_person_not_found(): void
    {
        $this->expectException(PersonNotFound::class);

        $this->persons
            ->expects($this->once())
            ->method('lockById')
            ->with($this->equalTo(1))
            ->willReturn(null)
        ;

        $info = new PersonInfoDto();
        $info->lastname = 'lastname';
        $info->firstname = 'firstname';
        $info->birthday = '1989-01-01';
        $info->clubId = '1';

        $command = new UpdatePersonInfo('1', $info, new UserId(1));
        $this->service->execute($command);
    }

    /** @test */
    public function it_updates_person_info(): void
    {
        /** @var Person $person */
        $person = Person::factory()->makeOne();

        $this->persons
            ->expects($this->once())
            ->method('lockById')
            ->with($this->equalTo(1))
            ->willReturn($person)
        ;

        $this->persons->expects($this->once())->method('update');

        $info = new PersonInfoDto();
        $info->lastname = 'lastname';
        $info->firstname = 'firstname';
        $info->birthday = '1989-01-01';
        $info->clubId = '1';

        $command = new UpdatePersonInfo('1', $info, new UserId(1));
        $person = $this->service->execute($command);

        $this->assertEquals('lastname', $person->lastname);
        $this->assertEquals('firstname', $person->firstname);
        $this->assertEquals('1989-01-01', $person->birthday);
        $this->assertEquals('1', $person->clubId);
    }
}
