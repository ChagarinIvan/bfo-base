<?php

declare(strict_types=1);

namespace Tests\Feature\RankCheck;

use App\Application\Handler\RankCheck\RankCheckCreatedHandler;
use App\Domain\Auth\Impression;
use App\Domain\RankCheck\Event\RankCheckCreated;
use App\Domain\RankCheck\Exception\UnableToProcess;
use App\Domain\RankCheck\RankCheck;
use App\Domain\RankCheck\RankCheckProcessor;
use App\Domain\RankCheck\RankCheckRow;
use App\Domain\RankCheck\RankCheckStatus;
use Carbon\Carbon;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Queue\CallQueuedHandler;
use Illuminate\Queue\Jobs\SyncJob;
use Illuminate\Queue\TimeoutExceededException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function json_encode;
use function serialize;

final class RankCheckQueueFailureTest extends TestCase
{
    use DatabaseTruncation;

    protected array $tablesToTruncate = ['rank_checks', 'rank_check_rows'];

    protected function tearDown(): void
    {
        RankCheck::query()->delete();
        parent::tearDown();
    }

    #[Test]
    public function a_terminal_timeout_rolls_back_partial_rows_and_persists_failed_status(): void
    {
        $check = $this->createCheck(RankCheckStatus::Parsing);
        $job = $this->queuedJob($check);
        $db = $this->app->make(ConnectionInterface::class);
        $db->beginTransaction();
        RankCheckRow::query()->create([
            'rank_check_id' => $check->id,
            'position' => 1,
            'name' => 'Partial result',
            'has_person' => false,
            'is_equal' => false,
        ]);

        $job->fail(TimeoutExceededException::forJob($job));

        $check->refresh();
        $this->assertSame(0, $db->transactionLevel());
        $this->assertSame(RankCheckStatus::Failed, $check->status);
        $this->assertSame('Не удалось обработать список разрядов.', $check->error_message);
        $this->assertSame(10, $check->updated->by);
        $this->assertTrue($check->updated->at->greaterThan($check->created->at));
        $this->assertDatabaseMissing('rank_check_rows', ['rank_check_id' => $check->id]);
    }

    #[Test]
    public function a_late_failure_does_not_overwrite_a_ready_result(): void
    {
        $check = $this->createCheck(RankCheckStatus::Ready);
        $updated = $check->updated;
        $job = $this->queuedJob($check);

        $job->fail(TimeoutExceededException::forJob($job));

        $check->refresh();
        $this->assertSame(RankCheckStatus::Ready, $check->status);
        $this->assertNull($check->error_message);
        $this->assertEquals($updated, $check->updated);
    }

    #[Test]
    public function duplicate_delivery_rejects_a_ready_check(): void
    {
        $this->assertTerminalCheckIsRejected(RankCheckStatus::Ready);
    }

    #[Test]
    public function duplicate_delivery_rejects_a_failed_check(): void
    {
        $this->assertTerminalCheckIsRejected(RankCheckStatus::Failed);
    }

    private function assertTerminalCheckIsRejected(RankCheckStatus $status): void
    {
        $check = $this->createCheck($status);
        $processor = $this->createMock(RankCheckProcessor::class);
        $processor->expects($this->never())->method('process');
        $this->app->instance(RankCheckProcessor::class, $processor);

        $this->expectException(UnableToProcess::class);
        try {
            $this->app->make(RankCheckCreatedHandler::class)->handle(new RankCheckCreated($check));
        } finally {
            $this->assertSame($status, $check->fresh()->status);
        }
    }

    private function createCheck(RankCheckStatus $status): RankCheck
    {
        $impression = new Impression(Carbon::now()->subMinute(), 10);
        $check = new RankCheck();
        $check->status = $status;
        $check->source_path = 'rank-checks/test.csv';
        $check->created = $impression;
        $check->updated = $impression;
        $check->save();

        return $check->fresh();
    }

    private function queuedJob(RankCheck $check): SyncJob
    {
        $listener = new CallQueuedListener(RankCheckCreatedHandler::class, 'handle', [new RankCheckCreated($check)]);

        return new SyncJob($this->app, json_encode([
            'job' => CallQueuedHandler::class . '@call',
            'uuid' => 'rank-check-timeout-test',
            'data' => [
                'commandName' => CallQueuedListener::class,
                'command' => serialize($listener),
            ],
        ], JSON_THROW_ON_ERROR), 'sync', 'rank-checks');
    }
}
