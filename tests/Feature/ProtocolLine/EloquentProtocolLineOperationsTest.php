<?php

declare(strict_types=1);

namespace Tests\Feature\ProtocolLine;

use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\Person\Person;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Infrastructure\Laravel\Eloquent\ProtocolLine\EloquentProtocolLineOperations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EloquentProtocolLineOperationsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function prepared_line_match_updates_only_the_requested_event(): void
    {
        [$current, $previousDistance, $currentDistance, $otherDistance] = $this->eventsAndDistances();
        $person = Person::factory()->createOne();
        $this->assertInstanceOf(Person::class, $person);
        ProtocolLine::factory()->createOne([
            'distance_id' => $previousDistance->id,
            'prepared_line' => 'same_runner_2000',
            'person_id' => $person->id,
        ]);
        $currentLine = ProtocolLine::factory()->createOne([
            'distance_id' => $currentDistance->id,
            'prepared_line' => 'same_runner_2000',
            'person_id' => null,
        ]);
        $otherLine = ProtocolLine::factory()->createOne([
            'distance_id' => $otherDistance->id,
            'prepared_line' => 'same_runner_2000',
            'person_id' => null,
        ]);

        $this->app->make(EloquentProtocolLineOperations::class)->identByEqualPreparedLine($current);

        $this->assertSame($person->id, $currentLine->fresh()->person_id);
        $this->assertNull($otherLine->fresh()->person_id);
    }

    #[Test]
    public function person_prompt_match_updates_only_the_requested_event(): void
    {
        [$current, , $currentDistance, $otherDistance] = $this->eventsAndDistances();
        $person = Person::factory()->createOne(['active' => true]);
        $this->assertInstanceOf(Person::class, $person);
        PersonPrompt::factory()->createOne([
            'person_id' => $person->id,
            'prompt' => 'prompt_runner_2000',
        ]);
        $currentLine = ProtocolLine::factory()->createOne([
            'distance_id' => $currentDistance->id,
            'prepared_line' => 'prompt_runner_2000',
            'person_id' => null,
        ]);
        $otherLine = ProtocolLine::factory()->createOne([
            'distance_id' => $otherDistance->id,
            'prepared_line' => 'prompt_runner_2000',
            'person_id' => null,
        ]);

        $this->app->make(EloquentProtocolLineOperations::class)->identByEqualPersonPrompt($current);

        $this->assertSame($person->id, $currentLine->fresh()->person_id);
        $this->assertNull($otherLine->fresh()->person_id);
    }

    /** @return array{Event, Distance, Distance, Distance} */
    private function eventsAndDistances(): array
    {
        $competition = Competition::factory()->createOne();
        $this->assertInstanceOf(Competition::class, $competition);
        $previous = Event::factory()->createOne(['competition_id' => $competition->id, 'date' => '2024-06-01']);
        $current = Event::factory()->createOne(['competition_id' => $competition->id, 'date' => '2026-06-10']);
        $other = Event::factory()->createOne(['competition_id' => $competition->id, 'date' => '2026-06-11']);
        $this->assertInstanceOf(Event::class, $previous);
        $this->assertInstanceOf(Event::class, $current);
        $this->assertInstanceOf(Event::class, $other);
        $group = Group::factory()->createOne();
        $this->assertInstanceOf(Group::class, $group);
        $previousDistance = Distance::factory()->createOne(['event_id' => $previous->id, 'group_id' => $group->id]);
        $currentDistance = Distance::factory()->createOne(['event_id' => $current->id, 'group_id' => $group->id]);
        $otherDistance = Distance::factory()->createOne(['event_id' => $other->id, 'group_id' => $group->id]);
        $this->assertInstanceOf(Distance::class, $previousDistance);
        $this->assertInstanceOf(Distance::class, $currentDistance);
        $this->assertInstanceOf(Distance::class, $otherDistance);

        return [$current, $previousDistance, $currentDistance, $otherDistance];
    }
}
