<?php

declare(strict_types=1);

namespace Tests\Application\Service\Rank;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\Exception\PersonNotFound;
use App\Application\Service\Person\RebuildPersonRanks;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Domain\Person\Audit\RankAuditRepository;
use App\Domain\Person\Audit\RankSnapshot;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRank;
use App\Domain\Person\PersonRanksUpdater;
use App\Domain\Person\PersonRepository;
use App\Domain\Person\RankCalculator;
use App\Domain\Person\RankFactsCollector;
use App\Domain\Rank\Rank;
use App\Domain\Shared\Clock;
use App\Domain\Shared\DummyTransactional;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RebuildPersonRanksServiceTest extends TestCase
{
    #[Test]
    public function it_rebuilds_one_person_and_persists_the_projection(): void
    {
        $person = $this->createStub(Person::class);
        $person->method('__get')->willReturnMap([['id', 42], ['birthday', null]]);
        $persons = $this->createMock(PersonRepository::class);
        $facts = $this->createMock(RankFactsCollector::class);
        $calculator = new RankCalculator();
        $clock = $this->createStub(Clock::class);
        $clock->method('now')->willReturn(Carbon::parse('2026-07-01'));
        $persons->expects($this->once())->method('lockById')->with(42)->willReturn($person);
        $facts->expects($this->once())->method('collect')->with(42)->willReturn([]);
        $persons->expects($this->once())->method('update')->with($person);

        $audit = $this->createStub(RankAuditRepository::class);
        $audit->method('snapshot')->willReturn(new RankSnapshot(new PersonRank(Rank::FirstRank, null, null, null), [], null));
        $service = new RebuildPersonRanksService($persons, new PersonRanksUpdater($persons, $facts, $calculator, $audit), $clock, new DummyTransactional);
        $service->execute(new RebuildPersonRanks(42, new UserId(1)));
    }

    #[Test]
    public function it_reports_a_missing_person(): void
    {
        $persons = $this->createMock(PersonRepository::class);
        $persons->expects($this->once())->method('lockById')->with(42)->willReturn(null);

        $this->expectException(PersonNotFound::class);

        new RebuildPersonRanksService(
            $persons,
            new PersonRanksUpdater($persons, $this->createStub(RankFactsCollector::class), new RankCalculator(), $this->createStub(RankAuditRepository::class)),
            $this->createStub(Clock::class),
            new DummyTransactional,
        )->execute(new RebuildPersonRanks(42, new UserId(1)));
    }
}
