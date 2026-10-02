<?php

declare(strict_types=1);

namespace Tests\Infrastructure\RankCheck;

use App\Application\Handler\RankCheck\RankCheckCreatedHandler;
use App\Domain\RankCheck\Event\RankCheckCreated;
use App\Domain\RankCheck\RankCheck;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\Factory;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Events\CallQueuedListener;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RankCheckQueueConfigurationTest extends TestCase
{
    #[Test]
    public function rank_checks_are_dispatched_with_a_safe_long_running_queue_budget(): void
    {
        $queue = $this->createMock(Queue::class);
        $factory = $this->createMock(Factory::class);
        $factory->expects($this->once())->method('connection')->with('redis-rank-checks')->willReturn($queue);
        $this->app->instance('queue', $factory);
        $queue->expects($this->once())->method('pushOn')->with(
            'rank-checks',
            $this->callback(function (CallQueuedListener $job): bool {
                $this->assertSame(RankCheckCreatedHandler::class, $job->class);
                $this->assertSame(300, $job->timeout);
                $this->assertSame(3, $job->tries);
                $this->assertTrue($job->afterCommit);

                return true;
            }),
        );

        $this->app->make(Dispatcher::class)->dispatch(new RankCheckCreated($this->createStub(RankCheck::class)));

        $config = $this->app->make('config');
        $supervisor = $config->get('horizon.defaults.supervisor-rank-checks');
        $this->assertSame('redis-rank-checks', $supervisor['connection']);
        $this->assertSame(['rank-checks'], $supervisor['queue']);
        $this->assertGreaterThan(300, $supervisor['timeout']);
        $this->assertGreaterThan($supervisor['timeout'], $config->get('queue.connections.redis-rank-checks.retry_after'));
        $this->assertArrayHasKey('supervisor-rank-checks', $config->get('horizon.environments.production'));
        $this->assertArrayHasKey('supervisor-rank-checks', $config->get('horizon.environments.local'));
    }
}
