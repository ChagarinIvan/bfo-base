<?php

declare(strict_types=1);

namespace Tests\Feature\ProtocolLine;

use App\Application\Handler\ProtocolLine\ProtocolLinePersonAssignedHandler;
use App\Application\Service\Person\DisablePersonService;
use App\Application\Service\ProtocolLine\SetPersonToProtocolLinesService;
use App\Domain\Auth\Impression;
use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\Person\Event\PersonDisabled;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRepository;
use App\Domain\ProtocolLine\Event\ProtocolLinePersonAssigned;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\FrozenClock;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Testing\Fakes\EventFake;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

final class ProtocolLinePersonAssignedHandlerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function repeated_delivery_does_not_disable_the_old_person_twice(): void
    {
        $events = new EventFake($this->app->make('events'));
        $this->app->instance('events', $events);
        [$event, $oldPerson] = $this->assignment();
        $handler = $this->app->make(ProtocolLinePersonAssignedHandler::class);

        $handler->handle($event);
        $disabled = $oldPerson->fresh();
        $this->assertSame(0, (int) $disabled->active);
        $handler->handle($event);

        $this->assertEquals($disabled->updated, $oldPerson->fresh()->updated);
        $events->assertDispatchedTimes(PersonDisabled::class, 1);
        $this->assertSame($event->protocolLine->person_id, $event->protocolLine->fresh()->person_id);
    }

    #[Test]
    public function an_unexpected_cleanup_storage_failure_is_not_swallowed(): void
    {
        $this->app->instance('events', new EventFake($this->app->make('events')));
        [$event, $oldPerson] = $this->assignment();
        $persons = $this->createMock(PersonRepository::class);
        $persons->expects($this->once())->method('lockById')->with($oldPerson->id)
            ->willThrowException(new RuntimeException('storage unavailable'));
        $handler = new ProtocolLinePersonAssignedHandler(
            $this->app->make(SetPersonToProtocolLinesService::class),
            $this->app->make(ProtocolLineRepository::class),
            new DisablePersonService($persons, new FrozenClock(), new DummyTransactional()),
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('storage unavailable');
        $handler->handle($event);
    }

    /** @return array{ProtocolLinePersonAssigned, Person} */
    private function assignment(): array
    {
        $competition = Competition::factory()->createOne();
        $event = Event::factory()->createOne(['competition_id' => $competition->id]);
        $group = Group::factory()->createOne();
        $distance = Distance::factory()->createOne(['event_id' => $event->id, 'group_id' => $group->id]);
        $oldPerson = Person::factory()->createOne(['id' => 1001]);
        $newPerson = Person::factory()->createOne(['id' => 1002]);
        $line = ProtocolLine::factory()->createOne([
            'distance_id' => $distance->id,
            'person_id' => $newPerson->id,
            'prepared_line' => 'synthetic runner',
            'complete_rank' => '',
        ]);

        return [new ProtocolLinePersonAssigned($line, $oldPerson->id, new Impression(Carbon::parse('2026-11-01'), 1)), $oldPerson];
    }
}
