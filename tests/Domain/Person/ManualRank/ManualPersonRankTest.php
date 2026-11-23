<?php

declare(strict_types=1);

namespace Tests\Domain\Person\ManualRank;

use App\Domain\Auth\Impression;
use App\Domain\Person\ManualRank\Exception\DuplicateManualRank;
use App\Domain\Person\ManualRank\Exception\InvalidManualRank;
use App\Domain\Person\ManualRank\ManualPersonRank;
use App\Domain\Person\ManualRank\ManualPersonRankFactory;
use App\Domain\Person\ManualRank\ManualPersonRankRepository;
use App\Domain\Person\ManualRank\ManualRankInput;
use App\Domain\Rank\Rank;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\FrozenClock;
use App\Domain\Shared\UuidGenerator;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ManualPersonRankTest extends TestCase
{
    #[Test]
    public function duplicate_policy_queries_the_active_source_tuple(): void
    {
        $input = new ManualRankInput(Rank::FirstRank, Carbon::parse('2026-01-01'), Carbon::parse('2026-01-10'));
        $audit = new Impression(Carbon::parse('2026-11-08'), 42);
        $repository = $this->createMock(ManualPersonRankRepository::class);
        $repository->expects($this->once())->method('oneByCriteria')->with(new Criteria(['personId' => 7, 'rank' => 6, 'achievedOn' => '2026-01-01', 'activatedOn' => '2026-01-10']))->willReturn($this->createStub(ManualPersonRank::class));
        $ids = $this->createMock(UuidGenerator::class);
        $ids->expects($this->never())->method('generate');
        $this->expectException(DuplicateManualRank::class);
        new ManualPersonRankFactory($repository, new FrozenClock($audit->at), $ids)->create(7, $input, 42);
    }

    #[Test]
    public function activation_before_achievement_is_rejected_in_domain(): void
    {
        $this->expectException(InvalidManualRank::class);
        new ManualRankInput(Rank::FirstRank, Carbon::parse('2026-01-10'), Carbon::parse('2026-01-01'));
    }
}
