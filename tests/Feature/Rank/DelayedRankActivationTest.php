<?php

declare(strict_types=1);

namespace Tests\Feature\Rank;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\RebuildExpiredPersonRanks;
use App\Application\Service\Person\RebuildExpiredPersonRanksService;
use App\Application\Service\Person\RebuildPersonRanks;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Event\StandardProtocolParser;
use App\Domain\Group\Group;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRankHistory;
use App\Domain\Person\RankFactsCollector;
use App\Domain\ProtocolLine\Factory\StandardProtocolLinesFactory;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use App\Domain\Shared\Clock;
use App\Domain\Shared\FrozenClock;
use App\Domain\Shared\Storage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DelayedRankActivationTest extends TestCase
{
    use RefreshDatabase;

    private int $nextDistanceId = 1000;

    #[Test]
    public function a_legacy_null_delay_policy_keeps_the_existing_activation_date(): void
    {
        $distance = $this->distance('2026-01-10');
        $person = Person::factory()->createOne();
        $line = $this->line($distance, $person, Rank::FirstRank, false, '2026-01-10');
        $scopes = ProtocolLine::getAllGlobalScopes();
        ProtocolLine::addGlobalScope('legacy-delay-policy', static function (Builder $query): void {
            $query->select('protocol_lines.*')->selectRaw('NULL AS delayed_rank_activation');
        });

        try {
            $this->assertNull(ProtocolLine::query()->findOrFail($line->id)->delayed_rank_activation);
            $facts = $this->app->make(RankFactsCollector::class)->collect($person->id);
            $this->assertCount(1, $facts);
            $this->assertFalse($facts[0]->delayedActivation);
            $this->assertSame('2026-01-10', $facts[0]->activatedOn?->toDateString());
            $this->at('2026-01-20');
            $this->rebuild($person);
            $this->assertSame(Rank::FirstRank, $person->fresh()->current_rank);
        } finally {
            ProtocolLine::setAllGlobalScopes($scopes);
        }
    }

    #[Test]
    public function a_parsed_protocol_persists_its_delay_policy_without_an_activation_date(): void
    {
        $distance = $this->distance('2026-01-10');
        $event = $distance->event;
        $event->file = 'protocol.html';
        $storage = $this->createMock(Storage::class);
        $storage->expects($this->once())->method('get')->with('protocol.html')->willReturn(<<<'HTML'
<div class="sportorg-table"></div>
<script>
var race = {"persons":[{"id":1,"group_id":1,"organization_id":1,"year":2000,"is_out_of_competition":false,"bib":1,"qual":1,"surname":"Тэст","name":"Спартсмен"}],"courses":[{"id":1,"length":1000,"controls":[1,2]}],"organizations":[{"id":1,"name":"  Тэст клуб  "}],"groups":[{"id":1,"name":"М21","course_id":1}],"results":[{"person_id":1,"result":"00:10:00","place":1,"assigned_rank":1,"status_comment":""}]};
var Qualification = {1:'II'};
</script>
HTML);
        $this->app->instance(Storage::class, $storage);
        $inputs = $this->app->make(StandardProtocolParser::class)->parse($event);

        $line = $this->app->make(StandardProtocolLinesFactory::class)->create($event, $inputs)[0];
        $line->save();

        $this->assertSame(Rank::SecondRank->label(), $line->fresh()->complete_rank);
        $this->assertTrue($line->fresh()->delayed_rank_activation);
        $this->assertNull($line->fresh()->activate_rank);
    }

    #[Test]
    public function all_five_new_ranks_wait_until_the_calendar_month_boundary_and_daily_rebuild_catches_up(): void
    {
        $distance = $this->distance('2026-01-31');
        $ranks = [Rank::SecondRank, Rank::ThirdRank, Rank::JuniorFirstRank, Rank::JuniorSecondRank, Rank::JuniorThirdRank];
        $people = [];
        $personId = 2000;

        foreach ($ranks as $rank) {
            $person = Person::factory()->createOne(['id' => ++$personId, 'birthday' => '2010-01-01']);
            $line = $this->line($distance, $person, $rank, true);
            $people[] = [$person, $line, $rank];
        }

        $this->at('2026-02-27');
        foreach ($people as [$person, $line]) {
            $this->rebuild($person);
            $person->refresh();
            $this->assertSame(Rank::WithoutRank, $person->current_rank);
            $this->assertSame('2026-02-28', $person->next_rank_activation_on?->toDateString());
            $this->assertNull(PersonRankHistory::query()->where('protocol_line_id', $line->id)->sole()->activated_on);
        }

        $this->at('2026-03-02');
        $count = $this->app->make(RebuildExpiredPersonRanksService::class)->execute(new RebuildExpiredPersonRanks(
            new UserId(1),
            ['rankFinishedBefore' => Carbon::parse('2026-03-02'), 'rankActivationDueBy' => Carbon::parse('2026-03-02')],
        ));

        $this->assertSame(5, $count);
        foreach ($people as [$person, $line, $rank]) {
            $person->refresh();
            $this->assertSame($rank, $person->current_rank);
            $this->assertNull($person->next_rank_activation_on);
            $this->assertSame('2026-02-28', PersonRankHistory::query()->where('protocol_line_id', $line->id)->sole()->activated_on?->toDateString());
            $this->assertNull($line->fresh()->activate_rank);
        }
        $this->assertSame(0, $this->app->make(RebuildExpiredPersonRanksService::class)->execute(new RebuildExpiredPersonRanks(
            new UserId(1),
            ['rankFinishedBefore' => Carbon::parse('2026-03-02'), 'rankActivationDueBy' => Carbon::parse('2026-03-02')],
        )));
    }

    #[Test]
    public function automatic_activation_is_applied_on_the_exact_due_day(): void
    {
        $distance = $this->distance('2026-01-10');
        $person = Person::factory()->createOne(['id' => 6001]);
        $line = $this->line($distance, $person, Rank::SecondRank, true);
        $this->at('2026-02-09');
        $this->rebuild($person);
        $this->assertSame(Rank::WithoutRank, $person->fresh()->current_rank);

        $this->at('2026-02-10');
        $count = $this->app->make(RebuildExpiredPersonRanksService::class)->execute(new RebuildExpiredPersonRanks(
            new UserId(1),
            ['rankFinishedBefore' => Carbon::parse('2026-02-10'), 'rankActivationDueBy' => Carbon::parse('2026-02-10')],
        ));

        $this->assertSame(1, $count);
        $this->assertSame(Rank::SecondRank, $person->fresh()->current_rank);
        $this->assertSame('2026-02-10', PersonRankHistory::query()->where('protocol_line_id', $line->id)->sole()->activated_on?->toDateString());
    }

    #[Test]
    public function old_dates_and_early_manual_dates_take_precedence_and_clearing_manual_date_restores_waiting(): void
    {
        $distance = $this->distance('2026-01-10');
        $old = Person::factory()->createOne(['id' => 2001]);
        $oldLine = $this->line($distance, $old, Rank::SecondRank, false, '2026-01-10');
        $oldCleared = Person::factory()->createOne(['id' => 2003]);
        $oldClearedLine = $this->line($distance, $oldCleared, Rank::ThirdRank, false);
        $new = Person::factory()->createOne(['id' => 2002]);
        $newLine = $this->line($distance, $new, Rank::SecondRank, true, '2026-01-20');
        $this->at('2026-01-25');
        $this->rebuild($old);
        $this->rebuild($oldCleared);
        $this->rebuild($new);

        $this->assertSame('2026-01-10', PersonRankHistory::query()->where('protocol_line_id', $oldLine->id)->sole()->activated_on?->toDateString());
        $this->assertSame('2026-01-10', PersonRankHistory::query()->where('protocol_line_id', $oldClearedLine->id)->sole()->activated_on?->toDateString());
        $this->assertSame('2026-01-20', PersonRankHistory::query()->where('protocol_line_id', $newLine->id)->sole()->activated_on?->toDateString());
        $this->assertNull($new->fresh()->next_rank_activation_on);

        $newLine->activate_rank = null;
        $newLine->save();
        $this->rebuild($new);
        $this->assertNull(PersonRankHistory::query()->where('protocol_line_id', $newLine->id)->sole()->activated_on);
        $this->assertSame('2026-02-10', $new->fresh()->next_rank_activation_on?->toDateString());
    }

    #[Test]
    public function a_future_manual_date_is_applied_by_the_daily_rebuild_on_that_date(): void
    {
        $distance = $this->distance('2026-01-10');
        $person = Person::factory()->createOne(['id' => 5001]);
        $line = $this->line($distance, $person, Rank::SecondRank, true, '2026-01-20');
        $this->at('2026-01-15');
        $this->rebuild($person);

        $this->assertSame(Rank::WithoutRank, $person->fresh()->current_rank);
        $this->assertSame('2026-01-20', $person->fresh()->next_rank_activation_on?->toDateString());

        $this->at('2026-01-20');
        $count = $this->app->make(RebuildExpiredPersonRanksService::class)->execute(new RebuildExpiredPersonRanks(
            new UserId(1),
            ['rankFinishedBefore' => Carbon::parse('2026-01-20'), 'rankActivationDueBy' => Carbon::parse('2026-01-20')],
        ));

        $this->assertSame(1, $count);
        $this->assertSame(Rank::SecondRank, $person->fresh()->current_rank);
        $this->assertNull($person->fresh()->next_rank_activation_on);
        $this->assertSame('2026-01-20', PersonRankHistory::query()->where('protocol_line_id', $line->id)->sole()->activated_on?->toDateString());
    }

    #[Test]
    public function a_pending_repeat_does_not_extend_a_rank_and_a_lower_due_rank_does_not_replace_a_higher_one(): void
    {
        $first = $this->distance('2025-01-10');
        $second = $this->distance('2026-01-10');
        $person = Person::factory()->createOne(['id' => 4001]);
        $old = $this->line($first, $person, Rank::SecondRank, false, '2025-01-10');
        $new = $this->line($second, $person, Rank::SecondRank, true);
        $higher = Person::factory()->createOne(['id' => 4002]);
        $this->line($first, $higher, Rank::FirstRank, false, '2025-01-10');
        $lower = $this->line($second, $higher, Rank::SecondRank, true);

        $this->at('2026-01-20');
        $this->rebuild($person);
        $this->rebuild($higher);
        $this->assertSame('2027-01-10', PersonRankHistory::query()->where('protocol_line_id', $old->id)->sole()->finished_on?->toDateString());
        $this->assertNull(PersonRankHistory::query()->where('protocol_line_id', $new->id)->sole()->activated_on);
        $this->assertSame(Rank::FirstRank, $higher->fresh()->current_rank);

        $this->at('2026-02-10');
        $this->rebuild($person);
        $this->rebuild($higher);
        $this->assertDatabaseHas('person_rank_histories', ['protocol_line_id' => $old->id, 'finished_on' => '2028-02-10']);
        $this->assertDatabaseHas('person_rank_histories', ['protocol_line_id' => $new->id, 'activated_on' => '2026-02-10']);
        $this->assertDatabaseHas('person_rank_histories', ['protocol_line_id' => $lower->id, 'activated_on' => '2026-02-10']);
        $this->assertSame(Rank::FirstRank, $higher->fresh()->current_rank);
    }

    #[Test]
    public function computed_junior_third_rank_is_immediate_while_an_explicit_one_waits(): void
    {
        $person = Person::factory()->createOne(['id' => 3001, 'birthday' => '2010-01-01']);
        foreach (['2026-01-10', '2026-01-20', '2026-01-30'] as $date) {
            $this->line($this->distance($date), $person, Rank::ThirdRank, true);
        }
        $explicitPerson = Person::factory()->createOne(['id' => 3002, 'birthday' => '2010-01-01']);
        $explicit = $this->line($this->distance('2026-01-30'), $explicitPerson, Rank::JuniorThirdRank, true);
        $this->at('2026-01-31');
        $this->rebuild($person);
        $this->rebuild($explicitPerson);

        $computed = PersonRankHistory::query()->where('person_id', $person->id)->where('rank', Rank::JuniorThirdRank->value)->sole();
        $this->assertSame('2026-01-30', $computed->activated_on?->toDateString());
        $this->assertSame(Rank::JuniorThirdRank, $person->fresh()->current_rank);
        $this->assertNull(PersonRankHistory::query()->where('protocol_line_id', $explicit->id)->sole()->activated_on);
        $this->assertSame('2026-02-28', $explicitPerson->fresh()->next_rank_activation_on?->toDateString());
    }

    private function distance(string $date): Distance
    {
        $id = ++$this->nextDistanceId;
        $competition = Competition::factory()->createOne(['id' => $id, 'mass' => false]);
        $event = Event::factory()->createOne(['id' => $id, 'competition_id' => $competition->id, 'date' => $date]);
        $group = Group::factory()->createOne(['id' => $id]);

        return Distance::factory()->createOne(['id' => $id, 'event_id' => $event->id, 'group_id' => $group->id]);
    }

    private function line(Distance $distance, Person $person, Rank $rank, bool $delayed, ?string $manualDate = null): ProtocolLine
    {
        return ProtocolLine::factory()->createOne([
            'distance_id' => $distance->id,
            'person_id' => $person->id,
            'complete_rank' => $rank->label(),
            'activate_rank' => $manualDate,
            'delayed_rank_activation' => $delayed,
        ]);
    }

    private function at(string $date): void
    {
        $this->app->instance(Clock::class, new FrozenClock(Carbon::parse($date)));
    }

    private function rebuild(Person $person): void
    {
        $this->app->make(RebuildPersonRanksService::class)->execute(new RebuildPersonRanks($person->id, new UserId(1)));
    }
}
