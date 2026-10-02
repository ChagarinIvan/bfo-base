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
use App\Domain\Rank\Rank;
use App\Infrastructure\Laravel\Eloquent\ProtocolLine\EloquentProtocolLineOperations;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
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

    /** @return iterable<string, array{Rank}> */
    public static function repeatedRanks(): iterable
    {
        yield 'KMS' => [Rank::CandidateMaster];
        yield 'MS' => [Rank::MasterOfSport];
    }

    #[Test]
    #[DataProvider('repeatedRanks')]
    public function it_activates_repeated_ranks_only_in_the_requested_event_with_one_update(Rank $rank): void
    {
        [$current, $previousDistance, $currentDistance, $otherDistance] = $this->eventsAndDistances();
        /** @var Person $person */
        $person = Person::factory()->createOne();
        ProtocolLine::factory()->count(2)->create([
            'distance_id' => $previousDistance->id,
            'person_id' => $person->id,
            'complete_rank' => $rank->label(),
            'activate_rank' => '2024-06-01',
        ]);
        /** @var ProtocolLine $currentLine */
        $currentLine = ProtocolLine::factory()->createOne([
            'distance_id' => $currentDistance->id,
            'person_id' => $person->id,
            'complete_rank' => $rank->label(),
            'activate_rank' => null,
        ]);
        /** @var ProtocolLine $otherLine */
        $otherLine = ProtocolLine::factory()->createOne([
            'distance_id' => $otherDistance->id,
            'person_id' => $person->id,
            'complete_rank' => $rank->label(),
            'activate_rank' => null,
        ]);
        $queries = [];
        $this->app->make(ConnectionInterface::class)->listen(static function (QueryExecuted $query) use (&$queries): void {
            $queries[] = $query->sql;
        });
        $operations = $this->app->make(EloquentProtocolLineOperations::class);

        $operations->activateEventLines($current);

        $this->assertCount(1, $queries);
        $this->assertStringStartsWith('update ', $queries[0]);
        $this->assertSame('2026-06-10', $currentLine->fresh()->activate_rank?->toDateString());
        $this->assertNull($otherLine->fresh()->activate_rank);

        $operations->activateEventLines($current);

        $this->assertSame('2026-06-10', $currentLine->fresh()->activate_rank?->toDateString());
    }

    /** @return iterable<string, array{bool, string, string|null, string, int|null, string|null}> */
    public static function linesThatMustRemainUnchanged(): iterable
    {
        yield 'first achievement' => [false, 'МС', null, '2024-06-01', 1, null];
        yield 'unactivated history' => [true, 'МС', null, '2024-06-01', 1, null];
        yield 'different rank' => [true, 'КМС', '2024-06-01', '2024-06-01', 1, null];
        yield 'same event date' => [true, 'МС', '2026-06-10', '2026-06-10', 1, null];
        yield 'future achievement' => [true, 'МС', '2027-06-10', '2027-06-10', 1, null];
        yield 'another person' => [true, 'МС', '2024-06-01', '2024-06-01', 2, null];
        yield 'unidentified line' => [true, 'МС', '2024-06-01', '2024-06-01', null, null];
        yield 'already activated' => [true, 'МС', '2024-06-01', '2024-06-01', 1, '2026-06-15'];
    }

    #[Test]
    #[DataProvider('linesThatMustRemainUnchanged')]
    public function it_preserves_lines_that_do_not_qualify_for_repeat_activation(
        bool $withHistory,
        string $previousRank,
        ?string $previousActivation,
        string $previousDate,
        ?int $personId,
        ?string $activation,
    ): void {
        [$current, $previousDistance, $currentDistance] = $this->eventsAndDistances();
        Person::factory()->createOne(['id' => 1]);
        Person::factory()->createOne(['id' => 2]);
        $previous = $previousDistance->event;
        $previous->date = $previousDate;
        $previous->save();
        if ($withHistory) {
            ProtocolLine::factory()->createOne([
                'distance_id' => $previousDistance->id,
                'person_id' => 1,
                'complete_rank' => $previousRank,
                'activate_rank' => $previousActivation,
            ]);
        }
        /** @var ProtocolLine $line */
        $line = ProtocolLine::factory()->createOne([
            'distance_id' => $currentDistance->id,
            'person_id' => $personId,
            'complete_rank' => Rank::MasterOfSport->label(),
            'activate_rank' => $activation,
        ]);

        $this->app->make(EloquentProtocolLineOperations::class)->activateEventLines($current);

        $this->assertSame($activation, $line->fresh()->activate_rank?->toDateString());
    }

    #[Test]
    public function it_does_not_activate_a_rank_outside_kms_and_ms(): void
    {
        [$current, $previousDistance, $currentDistance] = $this->eventsAndDistances();
        /** @var Person $person */
        $person = Person::factory()->createOne();
        ProtocolLine::factory()->createOne([
            'distance_id' => $previousDistance->id,
            'person_id' => $person->id,
            'complete_rank' => 'МСМК',
            'activate_rank' => '2024-06-01',
        ]);
        /** @var ProtocolLine $line */
        $line = ProtocolLine::factory()->createOne([
            'distance_id' => $currentDistance->id,
            'person_id' => $person->id,
            'complete_rank' => 'МСМК',
            'activate_rank' => null,
        ]);

        $this->app->make(EloquentProtocolLineOperations::class)->activateEventLines($current);

        $this->assertNull($line->fresh()->activate_rank);
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
