<?php

declare(strict_types=1);

namespace Tests\Domain\Rank;

use App\Domain\Person\Person;
use App\Domain\Person\PersonRepository;
use App\Domain\Rank\JuniorRankAgeValidator;
use App\Domain\Rank\Rank;
use App\Models\Year;
use Carbon\Carbon;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class JuniorRankAgeValidatorTest extends TestCase
{
    private PersonRepository&MockObject $personRepository;
    private JuniorRankAgeValidator $juniorRankAgeValidator;

    protected function setUp(): void
    {
        $this->personRepository = $this->createMock(PersonRepository::class);
        $this->juniorRankAgeValidator = new JuniorRankAgeValidator($this->personRepository);
    }

    /** @test */
    public function it_allows_all_not_junior_ranks(): void
    {
        $result = $this->juniorRankAgeValidator->validate(1, Rank::FIRST_RANK, Year::y2024);
        $this->personRepository->expects($this->never())->method('byId');

        $this->assertTrue($result);
    }

    /** @test */
    public function it_blocks_when_person_not_found(): void
    {
        $this->personRepository
            ->expects($this->once())
            ->method('byId')
            ->willReturn(null)
        ;
        $result = $this->juniorRankAgeValidator->validate(1, Rank::JUNIOR_FIRST_RANK, Year::y2000);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_allows_when_person_has_junior_age(): void
    {
        $person = new Person();
        $person->birthday = Carbon::createFromFormat('Y', '2006');

        $this->personRepository
            ->expects($this->once())
            ->method('byId')
            ->willReturn($person)
        ;

        $result = $this->juniorRankAgeValidator->validate(1, Rank::JUNIOR_FIRST_RANK, Year::y2024);
        $this->assertTrue($result);
    }

    /** @test */
    public function it_blocks_when_person_has_no_junior_age(): void
    {
        $person = new Person();
        $person->birthday = Carbon::createFromFormat('Y', '2005');

        $this->personRepository
            ->expects($this->once())
            ->method('byId')
            ->willReturn($person)
        ;

        $result = $this->juniorRankAgeValidator->validate(1, Rank::JUNIOR_FIRST_RANK, Year::y2024);
        $this->assertFalse($result);
    }
}
