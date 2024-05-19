<?php

declare(strict_types=1);

namespace Tests\Application\Service\Person;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\DisablePerson;
use App\Application\Service\Person\DisablePersonService;
use App\Application\Service\Person\Exception\PersonNotFound;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRepository;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\FrozenClock;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class DisablePersonServiceTest extends TestCase
{
    private DisablePersonService $service;

    private PersonRepository&MockObject $persons;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DisablePersonService(
            $this->persons = $this->createMock(PersonRepository::class),
            new FrozenClock(),
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

        $command = new DisablePerson('1', new UserId(1));
        $this->service->execute($command);
    }

    /** @test */
    public function it_disables_competition(): void
    {
        /** @var Person $person */
        $person = Person::factory()->makeOne();

        $this->persons
            ->expects($this->once())
            ->method('lockById')
            ->with($this->equalTo(1))
            ->willReturn($person)
        ;

        $this->persons
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($person))
        ;

        $command = new DisablePerson('1', new UserId(1));

        $this->service->execute($command);

        $this->assertFalse($person->active);
    }
}
