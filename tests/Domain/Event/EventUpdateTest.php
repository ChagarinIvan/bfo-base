<?php

declare(strict_types=1);

namespace Tests\Domain\Event;

use App\Domain\Auth\Impression;
use App\Domain\Event\Event;
use App\Domain\Event\Event\EventInfoUpdated;
use App\Domain\Event\Event\EventProtocolUpdated;
use App\Domain\Event\EventInfo;
use App\Domain\Event\EventProcessingStatus;
use App\Domain\Event\Protocol;
use App\Domain\Event\ProtocolUpdater;
use App\Domain\Event\UpdateInput;
use App\Domain\Shared\UuidGenerator;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EventUpdateTest extends TestCase
{
    #[Test]
    public function it_updates_info_and_releases_info_event(): void
    {
        $event = new Event;
        $event->updateInfo(
            new UpdateInput(new EventInfo('Updated', 'Description', Carbon::parse('2026-05-10'))),
            new Impression(Carbon::parse('2026-05-11'), 4),
        );

        $this->assertSame('Updated', $event->name);
        $this->assertSame('Description', $event->description);
        $this->assertContainsOnlyInstancesOf(EventInfoUpdated::class, $event->releasedEvents());
    }

    #[Test]
    public function it_updates_protocol_and_releases_protocol_event(): void
    {
        $event = new Event;
        $event->id = 42;
        $event->processing_status = EventProcessingStatus::READY;
        $event->processing_token = 'old-token';
        $event->error_message = 'Earlier failure';
        $updater = $this->createMock(ProtocolUpdater::class);
        $tokens = $this->createMock(UuidGenerator::class);
        $tokens->expects($this->once())->method('generate')->willReturn('new-token');
        $updater->expects($this->once())
            ->method('update')
            ->with($event, new Protocol('content', 'html'), $this->isInstanceOf(Impression::class))
            ->willReturn('protocol.html');

        $event->updateProtocol(
            $updater,
            new Protocol('content', 'html'),
            $tokens,
            new Impression(Carbon::parse('2026-05-11'), 4),
        );

        $this->assertSame('protocol.html', $event->file);
        $this->assertSame(EventProcessingStatus::PARSING, $event->processing_status);
        $this->assertSame('new-token', $event->processing_token);
        $this->assertNull($event->error_message);
        $this->assertCount(1, $event->releasedEvents());
        $updated = $event->releasedEvents()[0];
        $this->assertInstanceOf(EventProtocolUpdated::class, $updated);
        $this->assertSame(42, $updated->eventId);
        $this->assertSame('new-token', $updated->processingToken);
    }
}
