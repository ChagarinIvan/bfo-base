<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Jobs;

use App\Bridge\Laravel\Jobs\RebuildPersonRankJob;
use App\Bridge\Laravel\Jobs\RebuildPersonRanksJob;
use App\Domain\Auth\Impression;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\Queue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RebuildPersonRanksJobTest extends TestCase
{
    #[Test]
    public function it_dispatches_one_atomic_job_per_unique_person(): void
    {
        $impression = new Impression(Carbon::parse('2026-09-13 00:50:23'), 1);
        $queue = $this->createMock(Queue::class);
        $dispatchedPersonIds = [];
        $queue
            ->expects($this->exactly(2))
            ->method('push')
            ->willReturnCallback(function (object $job) use ($impression, &$dispatchedPersonIds): null {
                $this->assertInstanceOf(RebuildPersonRankJob::class, $job);
                $this->assertSame($impression, $job->impression);
                $dispatchedPersonIds[] = $job->personId;

                return null;
            })
        ;

        new RebuildPersonRanksJob([41846, 64375, 41846], $impression)->handle($queue);

        $this->assertSame([41846, 64375], $dispatchedPersonIds);
    }
}
