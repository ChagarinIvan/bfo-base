<?php

declare(strict_types=1);

namespace Tests\Domain\Event;

use App\Domain\Auth\Impression;
use App\Domain\Event\EventProtocol;
use App\Domain\Event\EventProtocolStatus;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EventProtocolTest extends TestCase
{
    #[Test]
    public function it_reaches_ready_only_after_all_lines_and_its_own_rank_batch(): void
    {
        $protocol = EventProtocol::queue(10, 'run-1');
        $parsingImpression = new Impression(Carbon::parse('2026-09-13 10:00:00'), 1);
        $identificationImpression = new Impression(Carbon::parse('2026-09-13 10:01:00'), 2);
        $protocol->startParsing($parsingImpression);
        $protocol->startIdentifying(2, $identificationImpression);

        $this->assertSame($identificationImpression, $protocol->updated);

        $this->assertTrue($protocol->recordIdentifiedLine(100, $identificationImpression));
        $this->assertFalse($protocol->startRankRebuild('batch-1', 2, $identificationImpression));
        $this->assertTrue($protocol->recordIdentifiedLine(101, $identificationImpression));
        $this->assertTrue($protocol->startRankRebuild('batch-1', 2, $identificationImpression));
        $this->assertFalse($protocol->completeRankJob('other-batch', $identificationImpression));
        $this->assertSame(EventProtocolStatus::REBUILDING_RANKS, $protocol->status);
        $this->assertTrue($protocol->completeRankJob('batch-1', $identificationImpression));
        $this->assertSame(EventProtocolStatus::REBUILDING_RANKS, $protocol->status);
        $this->assertTrue($protocol->completeRankJob('batch-1', $identificationImpression));
        $this->assertSame(EventProtocolStatus::READY, $protocol->status);
    }

    #[Test]
    public function it_ignores_duplicate_identification_and_stale_rank_completion(): void
    {
        $protocol = EventProtocol::queue(10, 'run-1');
        $impression = new Impression(Carbon::parse('2026-09-13'), 1);
        $protocol->startIdentifying(1, $impression);

        $this->assertTrue($protocol->recordIdentifiedLine(100, $impression));
        $this->assertFalse($protocol->recordIdentifiedLine(100, $impression));
        $this->assertSame(1, $protocol->identified_lines);
        $this->assertTrue($protocol->startRankRebuild('batch-1', 1, $impression));
        $this->assertFalse($protocol->completeRankJob('stale-batch', $impression));
    }
}
