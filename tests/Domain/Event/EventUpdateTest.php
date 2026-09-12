<?php

declare(strict_types=1);

namespace Tests\Domain\Event;

use App\Domain\Auth\Impression;
use App\Domain\Event\Event;
use App\Domain\Event\Event\EventInfoUpdated;
use App\Domain\Event\Event\EventProtocolUpdated;
use App\Domain\Event\EventInfo;
use App\Domain\Event\Protocol;
use App\Domain\Event\ProtocolUpdater;
use App\Domain\Event\UpdateInput;
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
        $updater = $this->createMock(ProtocolUpdater::class);
        $updater->expects($this->once())
            ->method('update')
            ->with($event, new Protocol('content', 'html'))
            ->willReturn('protocol.html');

        $event->updateProtocol(
            $updater,
            new Protocol('content', 'html'),
            new Impression(Carbon::parse('2026-05-11'), 4),
        );

        $this->assertSame('protocol.html', $event->file);
        $this->assertContainsOnlyInstancesOf(EventProtocolUpdated::class, $event->releasedEvents());
    }
}
