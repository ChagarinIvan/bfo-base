<?php

declare(strict_types=1);

namespace Tests\Application\Service\Person\ManualRank;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\ManualRank\ManualPersonRankAssembler;
use App\Application\Dto\Person\ManualRank\ManualPersonRankDto;
use App\Application\Service\Person\ManualRank\AddManualPersonRank;
use App\Application\Service\Person\ManualRank\AddManualPersonRankService;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Domain\Auth\Impression;
use App\Domain\Person\ManualRank\ManualPersonRank;
use App\Domain\Person\ManualRank\ManualPersonRankFactory;
use App\Domain\Person\ManualRank\ManualPersonRankRepository;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRankState;
use App\Domain\Person\PersonRepository;
use App\Domain\Person\RankCalculationReason;
use App\Domain\Person\RankCalculator;
use App\Domain\Person\RankFactsCollector;
use App\Domain\Rank\Rank;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\FrozenClock;
use App\Domain\Shared\UuidGenerator;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AddManualPersonRankServiceTest extends TestCase
{
    #[Test]
    public function it_persists_source_before_collecting_and_rebuilds_the_same_person(): void
    {
        $person = $this->createMock(Person::class);
        $person->expects($this->once())->method('updateRanks')->willReturnCallback(function (PersonRankState $state, Impression $impression, RankCalculationReason $reason): void {
            $this->assertSame(Rank::FirstRank, $state->current->rank);
            $this->assertSame('source-id', $state->history[0]->manual_rank_id);
            $this->assertSame(42, $impression->by);
            $this->assertSame(RankCalculationReason::ManualRankAdded, $reason);
        });
        $persons = $this->createMock(PersonRepository::class);
        $persons->expects($this->exactly(2))->method('lockById')->with(7)->willReturn($person);
        $persons->expects($this->once())->method('update')->with($person);
        $ranks = $this->createMock(ManualPersonRankRepository::class);
        $ranks->expects($this->once())->method('oneByCriteria')->willReturn(null);
        $stored = null;
        $ranks->expects($this->once())->method('add')->willReturnCallback(static function (ManualPersonRank $source) use (&$stored): void { $stored = $source; });
        $facts = $this->createMock(RankFactsCollector::class);
        $facts->expects($this->once())->method('collect')->with(7)->willReturnCallback(static function () use (&$stored): array { return [$stored->fact()]; });
        $ids = $this->createStub(UuidGenerator::class);
        $ids->method('generate')->willReturn('source-id');
        $clock = new FrozenClock(Carbon::parse('2026-11-08'));
        $transaction = new DummyTransactional();
        $service = new AddManualPersonRankService(
            $persons,
            $ranks,
            new ManualPersonRankFactory($ranks, $clock, $ids),
            new RebuildPersonRanksService($persons, $facts, new RankCalculator(), $clock, $transaction),
            $transaction,
            new ManualPersonRankAssembler(new AuthAssembler())
        );
        $dto = new ManualPersonRankDto()->fromArray(['rankId' => 6, 'achievedOn' => '2026-01-01', 'activatedOn' => '2026-01-10']);
        $result = $service->execute(new AddManualPersonRank(7, $dto, new UserId(42)));
        $this->assertSame('source-id', $result->id);
        $this->assertSame('42', $result->created->by);
        $this->assertSame('2026-01-10', $result->activatedOn);
    }
}
