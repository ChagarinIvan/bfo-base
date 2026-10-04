<?php

declare(strict_types=1);

namespace Tests\Feature\ProtocolLine;

use App\Domain\Auth\Impression;
use App\Domain\Club\Club;
use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Event\Event\EventIdentified;
use App\Domain\Event\Event\EventProcessingFailed;
use App\Domain\Event\EventProcessingStatus;
use App\Domain\Group\Group;
use App\Domain\Person\Citizenship;
use App\Domain\Person\Event\PersonCreated;
use App\Domain\Person\Exception\PersonInfoAlreadyExist;
use App\Domain\Person\Factory\PersonFactory;
use App\Domain\Person\Person;
use App\Domain\Person\PersonInfo;
use App\Domain\PersonPrompt\PromptIdentifier;
use App\Domain\ProtocolLine\Event\ProtocolLinePersonSet;
use App\Domain\ProtocolLine\Exception\IdentifyingError;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineIdentifier;
use App\Domain\Rank\Rank;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event as EventFacade;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StandardProtocolLineIdentifierTest extends TestCase
{
    use RefreshDatabase;

    /** @return iterable<string, array{bool}> */
    public static function identificationPaths(): iterable
    {
        yield 'fast matching only' => [true];
        yield 'prompt matching' => [false];
    }

    protected function setUp(): void
    {
        parent::setUp();

        EventFacade::fake([PersonCreated::class, ProtocolLinePersonSet::class]);
    }

    #[Test]
    public function it_identifies_only_remaining_lines_of_the_event_and_advances_the_status(): void
    {
        [$event, $distance] = $this->eventAndDistance();
        [, $otherDistance] = $this->eventAndDistance();
        /** @var Person $person */
        $person = Person::factory()->createOne(['active' => true]);
        ProtocolLine::factory()->createOne([
            'distance_id' => $otherDistance->id,
            'prepared_line' => 'exact_match',
            'person_id' => $person->id,
        ]);
        /** @var ProtocolLine $exact */
        $exact = ProtocolLine::factory()->createOne([
            'distance_id' => $distance->id,
            'prepared_line' => 'exact_match',
        ]);
        /** @var ProtocolLine $fuzzy */
        $fuzzy = ProtocolLine::factory()->createOne([
            'distance_id' => $distance->id,
            'prepared_line' => 'fuzzy_match',
        ]);
        /** @var ProtocolLine $assigned */
        $assigned = ProtocolLine::factory()->createOne([
            'distance_id' => $distance->id,
            'prepared_line' => 'already_assigned',
            'person_id' => $person->id,
        ]);
        /** @var ProtocolLine $other */
        $other = ProtocolLine::factory()->createOne([
            'distance_id' => $otherDistance->id,
            'prepared_line' => 'fuzzy_match',
        ]);
        $prompts = $this->createMock(PromptIdentifier::class);
        $prompts->expects($this->once())->method('identPerson')->with('fuzzy_match')->willReturn($person->id);
        $this->app->instance(PromptIdentifier::class, $prompts);
        $factory = $this->createMock(PersonFactory::class);
        $factory->expects($this->never())->method('create');
        $this->app->instance(PersonFactory::class, $factory);
        $impression = $this->impression();
        $event->processing_status = EventProcessingStatus::IDENTIFYING;

        $event->ident($event->processing_token, $this->app->make(ProtocolLineIdentifier::class), $impression);

        $this->assertSame($person->id, $exact->fresh()->person_id);
        $this->assertSame($person->id, $fuzzy->fresh()->person_id);
        $this->assertSame($person->id, $assigned->fresh()->person_id);
        $this->assertNull($other->fresh()->person_id);
        $this->assertSame(EventProcessingStatus::REBUILDING_RANKS, $event->processing_status);
        $this->assertEquals($impression, $event->updated);
        $this->assertInstanceOf(EventIdentified::class, $event->releasedEvents()[0]);
    }

    #[Test]
    #[DataProvider('identificationPaths')]
    public function it_saves_repeat_activation_before_identification_completes(bool $fastMatching): void
    {
        [$event, $distance] = $this->eventAndDistance();
        [$previous, $previousDistance] = $this->eventAndDistance();
        $previous->date = Carbon::parse('2024-06-01');
        $previous->save();
        $event->date = Carbon::parse('2026-06-10');
        $event->processing_status = EventProcessingStatus::IDENTIFYING;
        $event->save();
        /** @var Person $person */
        $person = Person::factory()->createOne(['active' => true]);
        ProtocolLine::factory()->createOne([
            'distance_id' => $previousDistance->id,
            'person_id' => $person->id,
            'prepared_line' => 'previous_master',
            'complete_rank' => Rank::MasterOfSport->label(),
            'activate_rank' => '2024-06-01',
        ]);
        /** @var ProtocolLine $line */
        $line = ProtocolLine::factory()->createOne([
            'distance_id' => $distance->id,
            'prepared_line' => $fastMatching ? 'previous_master' : 'new_master',
            'complete_rank' => Rank::MasterOfSport->label(),
            'activate_rank' => null,
        ]);
        $prompts = $this->createMock(PromptIdentifier::class);
        $prompts->expects($fastMatching ? $this->never() : $this->once())
            ->method('identPerson')->with('new_master')->willReturn($person->id);
        $this->app->instance(PromptIdentifier::class, $prompts);
        $factory = $this->createMock(PersonFactory::class);
        $factory->expects($this->never())->method('create');
        $this->app->instance(PersonFactory::class, $factory);

        $event->ident($event->processing_token, $this->app->make(ProtocolLineIdentifier::class), $this->impression());

        $this->assertSame($person->id, $line->fresh()->person_id);
        $this->assertSame('2026-06-10', $line->fresh()->activate_rank?->toDateString());
        $this->assertSame(EventProcessingStatus::REBUILDING_RANKS, $event->processing_status);
        $this->assertInstanceOf(EventIdentified::class, $event->releasedEvents()[0]);
    }

    #[Test]
    public function it_creates_one_person_for_repeated_lines_with_normalized_club_and_birth_year(): void
    {
        [$event, $distance] = $this->eventAndDistance();
        /** @var Club $club */
        $club = Club::factory()->createOne(['name' => 'БГУ', 'normalize_name' => 'бгу', 'active' => true]);
        $lines = ProtocolLine::factory()->count(2)->create([
            'distance_id' => $distance->id,
            'prepared_line' => 'new_runner_2001',
            'firstname' => 'Иван',
            'lastname' => 'Иванов',
            'year' => 2001,
            'club' => ' БДУ ',
        ]);
        $prompts = $this->createMock(PromptIdentifier::class);
        $prompts->expects($this->once())->method('identPerson')->with('new_runner_2001')->willReturn(null);
        $this->app->instance(PromptIdentifier::class, $prompts);
        $identifier = $this->app->make(ProtocolLineIdentifier::class);

        $identifier->identify($event, $this->impression());
        $identifier->identify($event, $this->impression());

        $this->assertSame(1, Person::query()->count());
        /** @var Person $person */
        $person = Person::query()->sole();
        $this->assertSame('Иван', $person->firstname);
        $this->assertSame('Иванов', $person->lastname);
        $this->assertNotNull($person->birthday);
        $this->assertSame('2001-01-01', $person->birthday->toDateString());
        $this->assertSame($club->id, $person->club_id);
        $this->assertSame(Citizenship::BELARUS, $person->citizenship);
        $this->assertFalse((bool) $person->from_base);
        $this->assertTrue((bool) $person->active);
        $this->assertSame($this->impression()->by, $person->created->by);
        $this->assertEquals($person->created, $person->updated);
        foreach ($lines as $line) {
            $this->assertSame($person->id, $line->fresh()->person_id);
        }
    }

    #[Test]
    public function it_creates_a_person_when_birth_year_and_matching_club_are_missing(): void
    {
        [$event, $distance] = $this->eventAndDistance();
        /** @var ProtocolLine $line */
        $line = ProtocolLine::factory()->createOne([
            'distance_id' => $distance->id,
            'prepared_line' => 'runner_without_birth_year',
            'year' => null,
            'club' => '',
        ]);
        $prompts = $this->createMock(PromptIdentifier::class);
        $prompts->expects($this->once())->method('identPerson')->willReturn(null);
        $this->app->instance(PromptIdentifier::class, $prompts);

        $this->app->make(ProtocolLineIdentifier::class)->identify($event, $this->impression());

        /** @var Person $person */
        $person = Person::query()->sole();
        $this->assertNull($person->birthday);
        $this->assertNull($person->club_id);
        $this->assertSame($person->id, $line->fresh()->person_id);
    }

    #[Test]
    public function it_wraps_duplicate_person_errors_and_records_the_domain_error_status(): void
    {
        [$event, $distance] = $this->eventAndDistance();
        /** @var ProtocolLine $line */
        $line = ProtocolLine::factory()->createOne(['distance_id' => $distance->id]);
        $cause = PersonInfoAlreadyExist::byInfo(
            new PersonInfo($line->firstname, $line->lastname, null, Citizenship::BELARUS, null),
            123,
        );
        $prompts = $this->createMock(PromptIdentifier::class);
        $prompts->method('identPerson')->willReturn(null);
        $this->app->instance(PromptIdentifier::class, $prompts);
        $factory = $this->createMock(PersonFactory::class);
        $factory->method('create')->willThrowException($cause);
        $this->app->instance(PersonFactory::class, $factory);
        $identifier = $this->app->make(ProtocolLineIdentifier::class);

        try {
            $identifier->identify($event, $this->impression());
            $this->fail('Expected an identification error.');
        } catch (IdentifyingError $error) {
            $this->assertSame($cause, $error->getPrevious());
        }
        $event->processing_status = EventProcessingStatus::IDENTIFYING;
        $impression = $this->impression();

        $event->ident($event->processing_token, $identifier, $impression);

        $this->assertSame(EventProcessingStatus::IDENTIFYING_ERROR, $event->processing_status);
        $this->assertSame('Person creation error: ' . $cause->getMessage(), $event->error_message);
        $this->assertEquals($impression, $event->updated);
        $this->assertInstanceOf(EventProcessingFailed::class, $event->releasedEvents()[0]);
        $this->assertNull($line->fresh()->person_id);
        $this->assertSame(0, Person::query()->count());
    }

    /** @return array{Event, Distance} */
    private function eventAndDistance(): array
    {
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne(['active' => true]);
        /** @var Event $event */
        $event = Event::factory()->createOne(['competition_id' => $competition->id, 'active' => true]);
        /** @var Group $group */
        $group = Group::factory()->createOne();
        /** @var Distance $distance */
        $distance = Distance::factory()->createOne(['event_id' => $event->id, 'group_id' => $group->id]);

        return [$event, $distance];
    }

    private function impression(): Impression
    {
        return new Impression(Carbon::parse('2026-10-02 12:00:00'), 42);
    }
}
