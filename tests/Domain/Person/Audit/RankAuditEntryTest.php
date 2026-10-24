<?php

declare(strict_types=1);

namespace Tests\Domain\Person\Audit;

use App\Domain\Auth\Impression;
use App\Domain\Person\Audit\RankAuditEntry;
use App\Domain\Person\Audit\RankAuditTrigger;
use App\Domain\Person\Audit\RankHistorySnapshot;
use App\Domain\Person\Audit\RankSnapshot;
use App\Domain\Person\PersonRank;
use App\Domain\Person\RankChangeType;
use App\Domain\Rank\Rank;
use Carbon\Carbon;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RankAuditEntryTest extends TestCase
{
    #[Test]
    public function it_keeps_the_trigger_impression_and_typed_states_without_duplicate_metadata(): void
    {
        $before = new RankSnapshot(new PersonRank(Rank::FirstRank, null, null, null), [], null);
        $after = new RankSnapshot(new PersonRank(Rank::CandidateMaster, null, null, null), [], null);
        $created = new Impression(Carbon::parse('2026-10-24 12:00:00'), 42);
        $entry = new RankAuditEntry(1, RankAuditTrigger::Activation, $created, $before, $after);
        $this->assertSame(RankAuditTrigger::Activation, $entry->trigger);
        $this->assertSame($created, $entry->created);
        $this->assertSame(Rank::FirstRank, $entry->before->current->rank);
        $this->assertSame(Rank::CandidateMaster, $entry->after->current->rank);
        $this->assertFalse($before->equals($after));
    }

    #[Test]
    public function snapshot_equality_uses_content_and_canonical_history_order(): void
    {
        $rank = new PersonRank(Rank::FirstRank, null, null, null);
        $first = $this->row(1);
        $second = $this->row(2);
        $before = new RankSnapshot($rank, [$first, $second], null);
        $after = new RankSnapshot(new PersonRank(Rank::FirstRank, null, null, null), [$second, $first], null);
        $this->assertTrue($before->equals($after));
        $this->assertFalse($before->equals(new RankSnapshot($rank, [$first], null)));
    }

    #[Test]
    public function an_error_may_have_no_result_but_a_successful_change_requires_a_previous_state(): void
    {
        $created = new Impression(Carbon::parse('2026-10-24'), 42);
        $entry = new RankAuditEntry(1, RankAuditTrigger::Manual, $created, null, null);
        $this->assertNull($entry->after);
        $this->expectException(InvalidArgumentException::class);
        new RankAuditEntry(1, RankAuditTrigger::Manual, $created, null, new RankSnapshot(new PersonRank(Rank::WithoutRank, null, null, null), [], null));
    }

    private function row(int $id): RankHistorySnapshot
    {
        return new RankHistorySnapshot(
            $id,
            $id,
            $id,
            $id,
            Rank::FirstRank,
            RankChangeType::Completion,
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-02-01'),
            Carbon::parse('2026-02-01'),
            Carbon::parse('2028-02-01')
        );
    }
}
