<?php

declare(strict_types=1);

namespace Tests\Application\Service\Person;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Person\PersonAssembler;
use App\Application\Dto\Person\ViewPersonDto;
use App\Application\Service\Person\Exception\PersonNotFound;
use App\Application\Service\Person\ViewPerson;
use App\Application\Service\Person\ViewPersonService;
use App\Domain\Auth\Impression;
use App\Domain\Person\Citizenship;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRank;
use App\Domain\Person\PersonRepository;
use App\Domain\Rank\Rank;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ViewPersonServiceTest extends TestCase
{
    private MockObject&PersonRepository $persons;

    private ViewPersonService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->persons = $this->createMock(PersonRepository::class);
        $this->service = new ViewPersonService($this->persons, new PersonAssembler(new AuthAssembler));
    }

    #[Test]
    public function it_fails_when_person_does_not_exist(): void
    {
        $this->expectException(PersonNotFound::class);

        $this->persons
            ->expects($this->once())
            ->method('byId')
            ->with(1)
            ->willReturn(null)
        ;

        $this->service->execute(new ViewPerson('1'));
    }

    #[Test]
    public function it_views_person(): void
    {
        /** @var Person&MockObject $person */
        $person = $this->getMockBuilder(Person::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['currentRank'])
            ->getMock();
        $person->id = 42;
        $person->lastname = 'Doe';
        $person->firstname = 'Jane';
        $person->birthday = null;
        $person->citizenship = Citizenship::BELARUS;
        $person->club_id = null;
        $person->created = new Impression(Carbon::parse('2026-01-01'), 1);
        $person->updated = new Impression(Carbon::parse('2026-01-02'), 1);
        $person
            ->expects($this->once())
            ->method('currentRank')
            ->willReturn(new PersonRank(Rank::WithoutRank, null, null, null));

        $this->persons
            ->expects($this->once())
            ->method('byId')
            ->with($person->id)
            ->willReturn($person)
        ;

        $dto = $this->service->execute(new ViewPerson((string) $person->id));

        $this->assertInstanceOf(ViewPersonDto::class, $dto);
        $this->assertSame($person->lastname, $dto->lastname);
    }
}
