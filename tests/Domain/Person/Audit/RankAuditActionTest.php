<?php

declare(strict_types=1);

namespace Tests\Domain\Person\Audit;

use App\Domain\Auth\Impression;
use App\Domain\Person\Audit\RankAuditAction;
use App\Domain\Person\Audit\RankAuditTrigger;
use App\Domain\Person\PersonRank;
use App\Domain\Rank\Rank;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RankAuditActionTest extends TestCase
{
    #[Test]
    public function an_application_has_a_typed_result_even_when_no_rank_is_held(): void
    {
        $current = new PersonRank(Rank::WithoutRank, null, null, null);
        $created = new Impression(Carbon::parse('2026-10-24 12:00:00'), 42);
        $action = new RankAuditAction(1, RankAuditTrigger::Manual, $created, $current, null);

        $this->assertSame($current, $action->current);
        $this->assertSame(Rank::WithoutRank, $action->current->rank);
        $this->assertSame($created, $action->created);
        $this->assertSame(RankAuditTrigger::Manual, $action->trigger);
        $this->assertNull($action->nextActivationOn);
    }
}
