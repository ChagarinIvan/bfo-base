<?php

declare(strict_types=1);

namespace Tests\Feature\Event;

use App\Domain\Auth\Impression;
use App\Domain\Competition\Competition;
use App\Domain\Event\Event;
use App\Domain\Event\Event\EventProcessingFailed;
use App\Domain\Event\Event\EventRanksUpdated;
use App\Domain\Event\EventProcessingStatus;
use App\Domain\Person\EventPersonRankUpdater;
use App\Domain\Person\Exception\RanksUpdatingError;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EventUpdateRanksTest extends TestCase
{
    use RefreshDatabase;

    /** @return iterable<string, array{EventProcessingStatus, string}> */
    public static function obsoleteCalls(): iterable
    {
        yield 'wrong stage' => [EventProcessingStatus::IDENTIFYING, 'current-token'];
        yield 'stale token' => [EventProcessingStatus::REBUILDING_RANKS, 'stale-token'];
    }

    #[Test]
    public function it_marks_the_event_ready_after_the_updater_succeeds_and_ignores_a_repeat(): void
    {
        $event = $this->event();
        $impression = new Impression(Carbon::parse('2026-10-02 12:00:00'), 42);
        $updater = $this->createMock(EventPersonRankUpdater::class);
        $updater->expects($this->once())->method('update')->with($event, $impression);

        $event->updateRanks($event->processing_token, $updater, $impression);
        $event->updateRanks($event->processing_token, $updater, new Impression($impression->at->copy()->addMinute(), 43));

        $this->assertSame(EventProcessingStatus::READY, $event->processing_status);
        $this->assertEquals($impression, $event->updated);
        $this->assertCount(1, $event->releasedEvents());
        $recorded = $event->releasedEvents()[0];
        $this->assertInstanceOf(EventRanksUpdated::class, $recorded);
        $this->assertSame($event->id, $recorded->eventId);
        $this->assertSame($event->processing_token, $recorded->processingToken);
        $this->assertSame($impression, $recorded->impression);
    }

    #[Test]
    public function it_records_a_rank_update_error_in_the_event(): void
    {
        $event = $this->event();
        $impression = new Impression(Carbon::parse('2026-10-02 12:00:00'), 42);
        $updater = $this->createMock(EventPersonRankUpdater::class);
        $updater->expects($this->once())->method('update')->willThrowException(new RanksUpdatingError('Rank update failed.'));

        $event->updateRanks($event->processing_token, $updater, $impression);

        $this->assertSame(EventProcessingStatus::REBUILDING_RANKS_ERROR, $event->processing_status);
        $this->assertSame('Rank update failed.', $event->error_message);
        $this->assertEquals($impression, $event->updated);
        $this->assertCount(1, $event->releasedEvents());
        $recorded = $event->releasedEvents()[0];
        $this->assertInstanceOf(EventProcessingFailed::class, $recorded);
        $this->assertSame($event->id, $recorded->eventId);
        $this->assertSame($event->processing_token, $recorded->processingToken);
        $this->assertSame(EventProcessingStatus::REBUILDING_RANKS_ERROR, $recorded->status);
    }

    #[Test]
    #[DataProvider('obsoleteCalls')]
    public function it_ignores_an_obsolete_call(EventProcessingStatus $status, string $token): void
    {
        $event = $this->event();
        $event->processing_status = $status;
        $original = $event->updated;
        $updater = $this->createMock(EventPersonRankUpdater::class);
        $updater->expects($this->never())->method('update');

        $event->updateRanks($token, $updater, new Impression(Carbon::parse('2026-10-02 12:00:00'), 42));

        $this->assertSame($status, $event->processing_status);
        $this->assertEquals($original, $event->updated);
        $this->assertNull($event->error_message);
        $this->assertSame([], $event->releasedEvents());
    }

    private function event(): Event
    {
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne();
        /** @var Event $event */
        $event = Event::factory()->createOne([
            'competition_id' => $competition->id,
            'processing_status' => EventProcessingStatus::REBUILDING_RANKS,
            'processing_token' => 'current-token',
            'error_message' => null,
        ]);

        return $event;
    }
}
