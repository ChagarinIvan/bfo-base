<?php

declare(strict_types=1);

namespace Tests\Domain\Rank;

use App\Domain\Person\Person;
use App\Domain\Person\PersonRepository;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Rank\Factory\RankFactory;
use App\Domain\Rank\JuniorRankAgeValidator;
use App\Domain\Rank\StandardJuniorJuniorThirdRankChecker;
use App\Domain\Shared\Clock;
use App\Domain\Shared\Criteria;
use App\Models\Year;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class StandardThirdRankCheckerTest extends TestCase
{
    private RankFactory&MockObject $factory;
    private PersonRepository&MockObject $persons;
    private ProtocolLineRepository&MockObject $protocols;
    private Clock&MockObject $clock;
    private StandardJuniorJuniorThirdRankChecker $checker;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(RankFactory::class);
        $this->persons = $this->createMock(PersonRepository::class);
        $this->protocols = $this->createMock(ProtocolLineRepository::class);
        $this->clock = $this->createMock(Clock::class);

        $this->checker = new StandardJuniorJuniorThirdRankChecker(
            $this->factory,
            $this->clock,
            new JuniorRankAgeValidator($this->persons),
            $this->protocols,
        );
    }

    /** @test */
    public function it_skips_check_for_non_juniors(): void
    {
        $person = new Person();
        $person->birthday = Carbon::createFromFormat('Y', '2005');

        $this->clock
            ->expects($this->exactly(2))
            ->method('years')
            ->willReturn([Year::y2024, Year::y2025])
        ;

        $this->clock
            ->expects($this->once())
            ->method('actualYear')
            ->willReturn(Year::y2025)
        ;

        $this->persons
            ->expects($this->once())
            ->method('byId')
            ->with(1)
            ->willReturnOnConsecutiveCalls($person, $person)
        ;

        $this->protocols->expects($this->never())->method('byCriteria');

        $result = $this->checker->check(1);

        $this->assertNull($result);
    }

    /** @test */
    public function it_skips_if_less_than_three_successful_starts(): void
    {
        $person = new Person();
        $person->birthday = Carbon::createFromFormat('Y', '2007');

        $this->clock
            ->expects($this->exactly(2))
            ->method('years')
            ->willReturn([Year::y2025])
        ;

        $this->clock
            ->expects($this->once())
            ->method('actualYear')
            ->willReturn(Year::y2025)
        ;

        $this->persons
            ->expects($this->once())
            ->method('byId')
            ->with(1)
            ->willReturn($person)
        ;

        $this->protocols
            ->expects($this->once())
            ->method('byCriteria')
            ->with(new Criteria(['personId' => 1, 'year' => Year::y2025]))
            ->willReturn(new Collection())
        ;

        $result = $this->checker->check(1);

        $this->assertNull($result);
    }
}
