<?php

declare(strict_types=1);

namespace Tests\Feature\ProtocolLine;

use App\Domain\Auth\Impression;
use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\Person\Person;
use App\Domain\ProtocolLine\Event\ProtocolLinePersonAssigned;
use App\Domain\ProtocolLine\Event\ProtocolLinePersonSet;
use App\Domain\ProtocolLine\Event\ProtocolLineRankActivated;
use App\Domain\ProtocolLine\Exception\UnableToActivateRank;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Testing\Fakes\EventFake;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ProtocolLineImpressionsTest extends TestCase
{
    use RefreshDatabase;

    public static function transitions(): iterable
    {
        yield 'assign person' => ['assignPerson', ProtocolLinePersonAssigned::class];
        yield 'set person' => ['setPerson', ProtocolLinePersonSet::class];
        yield 'activate rank' => ['activateRank', ProtocolLineRankActivated::class];
        yield 'clear activation' => ['clearRank', ProtocolLineRankActivated::class];
    }

    #[Test]
    #[DataProvider('transitions')]
    public function it_persists_the_transition_impression_and_preserves_creation(string $method, string $eventClass): void
    {
        $fake = new EventFake($this->app->make('events'));
        $this->app->instance('events', $fake);
        $line = $this->line();
        $created = $line->created;
        $impression = new Impression(Carbon::parse('2026-10-27 13:00:00'), 42);
        $person = Person::factory()->createOne();
        match ($method) {
            'activateRank' => $line->activateRank(Carbon::parse('2026-10-20'), $impression),
            'clearRank' => $line->activateRank(null, $impression),
            'assignPerson' => $line->assignPerson($person->id, $impression),
            'setPerson' => $line->setPerson($person->id, $impression),
            default => $this->fail('Unknown transition.'),
        };
        $this->assertEquals($impression, $line->updated);
        $repository = $this->app->make(ProtocolLineRepository::class);
        $repository->update($line);
        $repository->update($line);

        $fresh = $line->fresh();
        $this->assertEquals($created, $fresh->created);
        $this->assertEquals($impression, $fresh->updated);
        if ($method === 'assignPerson' || $method === 'setPerson') {
            $this->assertSame($person->id, $fresh->person_id);
        } else {
            $this->assertSame($method === 'clearRank' ? null : '2026-10-20', $fresh->activate_rank?->toDateString());
        }
        $fake->assertDispatchedTimes($eventClass, 1);
        $fake->assertDispatched($eventClass, static fn ($event): bool => $event->impression === $impression);
    }

    #[Test]
    public function a_rejected_activation_keeps_the_persisted_impressions_and_state(): void
    {
        $line = $this->line();
        $before = $line->getAttributes();
        try {
            $line->activateRank(Carbon::parse('2026-10-01'), new Impression(Carbon::parse('2026-10-27'), 42));
            $this->fail('Early activation must fail.');
        } catch (UnableToActivateRank) {
            $this->assertSame($before, $line->getAttributes());
            $this->assertSame([], $line->releasedEvents());
            $this->app->make(ProtocolLineRepository::class)->update($line);
            $this->assertEquals($line->created, $line->fresh()->created);
            $this->assertEquals($line->updated, $line->fresh()->updated);
        }
    }

    private function line(): ProtocolLine
    {
        $competition = Competition::factory()->createOne();
        $event = Event::factory()->createOne(['competition_id' => $competition->id, 'date' => '2026-10-10']);
        $group = Group::factory()->createOne();
        $distance = Distance::factory()->createOne(['event_id' => $event->id, 'group_id' => $group->id]);

        return ProtocolLine::factory()->createOne([
            'distance_id' => $distance->id,
            'created' => new Impression(Carbon::parse('2026-10-11 10:00:00'), 7),
            'updated' => new Impression(Carbon::parse('2026-10-12 11:00:00'), 8),
            'activate_rank' => '2026-10-15',
        ]);
    }
}
