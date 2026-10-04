<?php

declare(strict_types=1);

namespace Tests\Feature\Event;

use App\Domain\Auth\Impression;
use App\Domain\Competition\Competition;
use App\Domain\Event\Event;
use App\Domain\Event\Event\EventDisabled;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event as EventFacade;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EventAggregateDispatchTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_dispatches_a_recorded_event_only_once_across_two_saves(): void
    {
        EventFacade::fake([EventDisabled::class]);
        $competition = Competition::factory()->createOne();
        $event = Event::factory()->createOne(['competition_id' => $competition->id]);

        $event->disable(new Impression(Carbon::parse('2026-05-11'), 4));
        $this->assertCount(1, $event->releasedEvents());

        $event->save();
        $this->assertSame([], $event->releasedEvents());

        $event->save();
        EventFacade::assertDispatchedTimes(EventDisabled::class, 1);
        $this->assertSame([], $event->releasedEvents());
    }
}
