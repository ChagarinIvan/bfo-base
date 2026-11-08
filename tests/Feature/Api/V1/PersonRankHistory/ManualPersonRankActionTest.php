<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\PersonRankHistory;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Event\CleanupEventResults;
use App\Application\Service\Event\CleanupEventResultsService;
use App\Application\Service\Person\RebuildExpiredPersonRanks;
use App\Application\Service\Person\RebuildExpiredPersonRanksService;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Person\ManualRank\AddManualPersonRankAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Person\ManualRank\DeleteManualPersonRankAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Person\PersonRankHistory\ListPersonRankHistoryAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Person\RebuildPersonRanksAction;
use App\Domain\Event\Event;
use App\Domain\Person\ManualRank\Event\ManualPersonRankAdded;
use App\Domain\Person\ManualRank\Event\ManualPersonRankDisabled;
use App\Domain\Person\Person;
use App\Domain\Person\RankCalculator;
use App\Domain\Person\RankFact;
use App\Domain\Person\RankFactsCollector;
use App\Domain\Rank\Rank;
use App\Domain\Shared\Clock;
use App\Domain\Shared\FrozenClock;
use App\Infrastructure\Sanctum\SanctumUser;
use Carbon\Carbon;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;
use function array_find;
use function array_slice;

/**
 * @see AddManualPersonRankAction
 * @see DeleteManualPersonRankAction
 * @see RebuildPersonRanksAction
 * @see ListPersonRankHistoryAction
 */
final class ManualPersonRankActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function manual_source_survives_rebuild_and_is_removed_only_by_disabling_it(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 8)->startOfDay());
        Sanctum::actingAs(SanctumUser::query()->create(['email' => fake()->unique()->safeEmail(), 'password' => 'unused']));
        $person = Person::factory()->createOne(['id' => 1001, 'birthday' => null]);
        $url = "/api/v1/persons/{$person->id}";
        $payload = ['rankId' => Rank::JuniorFirstRank->value, 'achievedOn' => '2026-01-01', 'activatedOn' => '2026-01-10'];
        $id = $this->postJson("{$url}/manual-ranks", $payload)->assertCreated()->json('id');
        $this->assertSame(Rank::JuniorFirstRank, $person->fresh()->currentRank()->rank);
        for ($i = 0; $i < 2; ++$i) {
            $this->postJson("{$url}/ranks/rebuild")->assertNoContent();
            $this->getJson("{$url}/rank-histories")->assertOk()->assertJsonCount(1)
                ->assertJsonPath('0.manualRankId', $id)->assertJsonPath('0.source', 'manual')
                ->assertJsonPath('0.protocolLineId', null)->assertJsonPath('0.eventId', null)
                ->assertJsonPath('0.activatedOn', '2026-01-10');
        }
        $this->postJson("{$url}/manual-ranks", $payload)->assertConflict();
        $other = Person::factory()->createOne(['id' => 1002]);
        $this->deleteJson("/api/v1/persons/{$other->id}/manual-ranks/{$id}")->assertNotFound();
        $this->deleteJson("{$url}/manual-ranks/{$id}")->assertNoContent();
        $this->assertDatabaseHas('manual_person_ranks', ['id' => $id, 'active' => false]);
        $this->assertSame(Rank::WithoutRank, $person->fresh()->currentRank()->rank);
        $this->getJson("{$url}/rank-histories")->assertExactJson([]);
        $this->postJson("{$url}/manual-ranks", $payload)->assertCreated();
    }

    #[Test]
    public function it_rejects_guest_writes_and_invalid_dates_without_creating_a_source(): void
    {
        $person = Person::factory()->createOne(['id' => 1001]);
        $url = "/api/v1/persons/{$person->id}/manual-ranks";
        $this->postJson($url, [])->assertUnauthorized();
        $this->deleteJson("{$url}/missing")->assertUnauthorized();
        Sanctum::actingAs(SanctumUser::query()->create(['email' => fake()->unique()->safeEmail(), 'password' => 'unused']));
        foreach ([
            ['rankId' => 0, 'achievedOn' => '2026-01-01', 'activatedOn' => '2026-01-01'],
            ['rankId' => 6, 'achievedOn' => '2026-01-02', 'activatedOn' => '2026-01-01'],
            ['rankId' => 6, 'achievedOn' => '2099-01-01', 'activatedOn' => '2099-01-01'],
        ] as $payload) {
            $this->postJson($url, $payload)->assertUnprocessable();
        }
        $this->assertDatabaseCount('manual_person_ranks', 0);
    }
    #[Test]
    public function distinct_manual_sources_extend_and_future_activation_is_picked_up_daily(): void
    {
        $this->travelTo(Carbon::parse('2026-11-08'));
        $this->app->instance(Clock::class, new FrozenClock(Carbon::parse('2026-11-08')));
        Sanctum::actingAs(SanctumUser::query()->create(['email' => fake()->unique()->safeEmail(), 'password' => 'unused']));
        $person = Person::factory()->createOne(['id' => 1001]);
        $url = "/api/v1/persons/{$person->id}";
        foreach (['2026-01-01', '2026-03-01'] as $date) {
            $this->postJson("{$url}/manual-ranks", ['rankId' => 6, 'achievedOn' => $date, 'activatedOn' => $date])->assertCreated();
        }
        $this->getJson("{$url}/rank-histories")->assertJsonCount(2)
            ->assertJsonPath('0.finishedOn', '2028-03-01')->assertJsonPath('1.finishedOn', '2028-03-01');
        $this->postJson("{$url}/manual-ranks", ['rankId' => 7, 'achievedOn' => '2026-10-01', 'activatedOn' => '2026-11-10'])->assertCreated();
        $this->assertSame(Rank::FirstRank, $person->fresh()->currentRank()->rank);
        $this->assertSame('2026-11-10', $person->fresh()->next_rank_activation_on?->format('Y-m-d'));
        $this->app->instance(Clock::class, new FrozenClock(Carbon::parse('2026-11-10')));
        $this->app->make(RebuildExpiredPersonRanksService::class)->execute(new RebuildExpiredPersonRanks(new UserId(42), ['rankActivationDueBy' => Carbon::parse('2026-11-10')]));
        $this->assertSame(Rank::CandidateMaster, $person->fresh()->currentRank()->rank);
    }

    #[Test]
    public function manual_fact_does_not_become_a_third_protocol_start_and_keeps_source_through_mixed_promotion(): void
    {
        $person = Person::factory()->createOne(['id' => 1001, 'birthday' => '2010-01-01']);
        $facts = [
            new RankFact(10, 20, 30, 40, Rank::WithoutRank, Carbon::parse('2026-01-01'), null),
            new RankFact(11, 21, 31, 41, Rank::WithoutRank, Carbon::parse('2026-02-01'), null),
            new RankFact(null, null, null, null, Rank::JuniorSecondRank, Carbon::parse('2026-03-01'), Carbon::parse('2026-03-01'), manualRankId: 'manual'),
            new RankFact(12, 22, 32, 42, Rank::FirstRank, Carbon::parse('2026-04-01'), Carbon::parse('2026-04-01')),
        ];
        // The adult promotion is the third real start. Its synthetic IIIю must retain protocol identity.
        $result = new RankCalculator()->calculate(array_slice($facts, 0, 3), $person, Carbon::parse('2026-11-08'));
        $this->assertCount(1, $result->history);
        $this->assertSame('manual', $result->history[0]->manual_rank_id);
        $result = new RankCalculator()->calculate($facts, $person, Carbon::parse('2026-11-08'));
        $manual = array_find($result->history, static fn ($entry): bool => $entry->manual_rank_id === 'manual');
        $this->assertNotNull($manual);
        $this->assertSame('2026-04-01', $manual->finished_on->format('Y-m-d'));
        $this->assertSame(Rank::FirstRank, $result->current->rank);
    }

    #[Test]
    public function failure_rolls_back_source_projection_and_audit_and_does_not_deliver_source_events(): void
    {
        $this->travelTo(Carbon::parse('2026-11-08'));
        Sanctum::actingAs(SanctumUser::query()->create(['email' => fake()->unique()->safeEmail(), 'password' => 'unused']));
        $person = Person::factory()->createOne(['id' => 1001]);
        $url = "/api/v1/persons/{$person->id}";
        $payload = ['rankId' => 6, 'achievedOn' => '2026-01-01', 'activatedOn' => '2026-01-01'];
        $id = $this->postJson("{$url}/manual-ranks", $payload)->assertCreated()->json('id');
        $before = $person->fresh()->getAttributes();
        $events = [];
        $dispatcher = $this->app->make(Dispatcher::class);
        $dispatcher->listen(ManualPersonRankAdded::class, static function ($event) use (&$events): void { $events[] = $event; });
        $dispatcher->listen(ManualPersonRankDisabled::class, static function ($event) use (&$events): void { $events[] = $event; });
        $collector = $this->createMock(RankFactsCollector::class);
        $collector->expects($this->exactly(2))->method('collect')->willThrowException(new RuntimeException('failed after source save'));
        $this->app->instance(RankFactsCollector::class, $collector);
        $this->withoutExceptionHandling();
        foreach (['add', 'delete'] as $operation) {
            try {
                if ($operation === 'add') {
                    $this->postJson("{$url}/manual-ranks", [...$payload, 'rankId' => 7]);
                } else {
                    $this->deleteJson("{$url}/manual-ranks/{$id}");
                }
                $this->fail('Expected calculation failure');
            } catch (RuntimeException $error) {
                $this->assertSame('failed after source save', $error->getMessage());
            }
            $this->assertDatabaseCount('manual_person_ranks', 1);
            $this->assertDatabaseHas('manual_person_ranks', ['id' => $id, 'active' => true]);
            $this->assertDatabaseCount('person_rank_histories', 1);
            $this->assertSame($before, $person->fresh()->getAttributes());
            $this->assertSame([], $events);
        }
    }

    #[Test]
    public function same_day_mixed_facts_have_stable_protocol_first_order(): void
    {
        $person = Person::factory()->createOne(['id' => 1001]);
        $date = Carbon::parse('2026-01-01');
        $protocol = new RankFact(1, 1, 1, 1, Rank::FirstRank, $date, $date);
        $manual = new RankFact(null, null, null, null, Rank::SecondRank, $date, $date, manualRankId: 'manual');
        foreach ([[$manual, $protocol], [$protocol, $manual]] as $facts) {
            $history = new RankCalculator()->calculate($facts, $person, Carbon::parse('2026-11-08'))->history;
            $this->assertSame(1, $history[0]->protocol_line_id);
            $this->assertSame('manual', $history[1]->manual_rank_id);
        }
    }

    #[Test]
    public function refill_and_actual_protocol_cleanup_keep_the_manual_source(): void
    {
        $this->travelTo(Carbon::parse('2026-11-08'));
        $this->seed(ProtocolLinesSeeder::class);
        Sanctum::actingAs(SanctumUser::query()->create(['email' => fake()->unique()->safeEmail(), 'password' => 'unused']));
        $id = $this->postJson('/api/v1/persons/101/manual-ranks', ['rankId' => 6, 'achievedOn' => '2026-01-01', 'activatedOn' => '2026-01-01'])->assertCreated()->json('id');
        $this->artisan('persons:ranks:refill', ['userId' => 42])->assertSuccessful();
        $this->assertDatabaseHas('person_rank_histories', ['person_id' => 101, 'manual_rank_id' => $id]);
        $event = Event::query()->findOrFail(102);
        $this->app->make(CleanupEventResultsService::class)->execute(new CleanupEventResults(102, $event->processing_token));
        $this->assertDatabaseHas('manual_person_ranks', ['id' => $id, 'active' => true]);
        $this->assertDatabaseHas('person_rank_histories', ['person_id' => 101, 'manual_rank_id' => $id, 'activated_on' => '2026-01-01']);
    }
    #[Test]
    public function automatic_pruning_preserves_a_person_with_an_active_manual_source(): void
    {
        $this->travelTo(Carbon::parse('2026-11-08'));
        Sanctum::actingAs(SanctumUser::query()->create(['email' => fake()->unique()->safeEmail(), 'password' => 'unused']));
        $person = Person::factory()->createOne(['id' => 1001]);
        $empty = Person::factory()->createOne(['id' => 1002]);
        $this->postJson("/api/v1/persons/{$person->id}/manual-ranks", ['rankId' => 6, 'achievedOn' => '2026-01-01', 'activatedOn' => '2026-01-01'])->assertCreated();
        $this->artisan('persons:prune', ['userId' => 42])->assertSuccessful();
        $this->assertSame(1, (int) $person->fresh()->active);
        $this->assertSame(0, (int) $empty->fresh()->active);
    }
}
