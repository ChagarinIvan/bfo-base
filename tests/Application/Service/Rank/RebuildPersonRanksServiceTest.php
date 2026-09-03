<?php

declare(strict_types=1);

namespace Tests\Application\Service\Rank;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\RebuildPersonRanks;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRepository;
use App\Domain\Person\RankCalculator;
use App\Domain\Person\RankFactsCollector;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;
use Carbon\Carbon;
use Closure;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RebuildPersonRanksServiceTest extends TestCase
{
    #[Test]
    public function it_rebuilds_one_person_and_persists_the_projection(): void
    {
        $person = $this->createStub(Person::class);
        $persons = $this->createMock(PersonRepository::class);
        $facts = $this->createMock(RankFactsCollector::class);
        $calculator = new RankCalculator();
        $clock = $this->createMock(Clock::class);
        $transactional = $this->createMock(TransactionManager::class);
        $clock->method('now')->willReturn(Carbon::parse('2026-07-01'));
        $persons->expects($this->once())->method('lockById')->with(42)->willReturn($person);
        $facts->expects($this->once())->method('collect')->with(42)->willReturn([]);
        $persons->expects($this->once())->method('update')->with($person);
        $transactional->expects($this->once())->method('run')->willReturnCallback(static fn (Closure $callback): mixed => $callback());

        $service = new RebuildPersonRanksService($persons, $facts, $calculator, $clock, $transactional);
        $service->execute(new RebuildPersonRanks(42, new UserId(1)));
    }
}
