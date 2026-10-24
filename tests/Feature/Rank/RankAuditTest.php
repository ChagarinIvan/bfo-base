<?php

declare(strict_types=1);

namespace Tests\Feature\Rank;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\RebuildPersonRanks;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Domain\Auth\Impression;
use App\Domain\Person\Audit\RankAuditAction;
use App\Domain\Person\Audit\RankAuditRepository;
use App\Domain\Person\Audit\RankAuditTrigger;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRank;
use App\Domain\Person\PersonRankHistory;
use App\Domain\Person\PersonRankState;
use App\Domain\Person\PersonRanksUpdater;
use App\Domain\Person\PersonRepository;
use App\Domain\Person\RankFactsCollector;
use App\Domain\Rank\Rank;
use App\Domain\Shared\Criteria;
use App\Infrastructure\Laravel\Eloquent\Person\RankAudit\RankAuditModel;
use Carbon\Carbon;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;
use function array_map;

final class RankAuditTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function each_successful_application_records_its_trigger_and_applied_result(): void
    {
        $person = Person::factory()->createOne(['current_rank' => Rank::FirstRank]);
        $service = $this->app->make(RebuildPersonRanksService::class);
        $service->execute(new RebuildPersonRanks($person->id, new UserId(1)));
        $service->execute(new RebuildPersonRanks($person->id, new UserId(2), RankAuditTrigger::Scheduled));
        $service->execute(new RebuildPersonRanks($person->id, new UserId(3)));

        $this->assertSame(3, RankAuditModel::query()->count());
        $entries = $this->app->make(RankAuditRepository::class)->byCriteria(new Criteria(['personId' => $person->id]))->items();
        $this->assertSame([3, 2, 1], array_map(static fn (RankAuditAction $entry): int => $entry->created->by, $entries));
        $this->assertSame(RankAuditTrigger::Scheduled, $entries[1]->trigger);
        foreach ($entries as $entry) {
            $this->assertSame(Rank::WithoutRank, $entry->current->rank);
            $this->assertNull($entry->nextActivationOn);
        }
    }

    #[Test]
    public function the_applied_result_round_trips_all_five_rank_fields(): void
    {
        $person = Person::factory()->createOne();
        $current = new PersonRank(
            Rank::CandidateMaster,
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-02-01'),
            Carbon::parse('2028-02-01'),
        );
        $next = Carbon::parse('2027-01-01');
        $person->updateRanks(new PersonRankState($current, [], $next), new Impression(Carbon::now(), 42), RankAuditTrigger::Activation);
        $this->app->make(PersonRepository::class)->update($person);

        $entry = $this->app->make(RankAuditRepository::class)->byCriteria(new Criteria(['personId' => $person->id]))->items()[0];
        $this->assertEquals($current, $entry->current);
        $this->assertEquals($next, $entry->nextActivationOn);
        $this->assertSame(RankAuditTrigger::Activation, $entry->trigger);
        $this->assertSame(42, $entry->created->by);
    }

    #[Test]
    public function saving_person_again_without_applying_ranks_does_not_duplicate_pending_audit(): void
    {
        $this->seed(ProtocolLinesSeeder::class);
        $person = Person::query()->findOrFail(101);
        $this->app->make(PersonRanksUpdater::class)->update($person, new Impression(Carbon::now(), 1), RankAuditTrigger::Manual);
        $this->assertSame([], $person->rankAudit);
        $this->assertNull($person->rankHistoryToPersist());
        $history = PersonRankHistory::query()->where('person_id', 101)->get()->toArray();
        $this->assertNotEmpty($history);
        $this->app->make(PersonRepository::class)->update($person);
        $this->assertSame($history, PersonRankHistory::query()->where('person_id', 101)->get()->toArray());

        $this->assertSame(1, RankAuditModel::query()->count());
    }

    #[Test]
    public function audit_storage_failure_rolls_back_projection_and_replaced_history_without_an_error_entry(): void
    {
        $this->seed(ProtocolLinesSeeder::class);
        $service = $this->app->make(RebuildPersonRanksService::class);
        $service->execute(new RebuildPersonRanks(101, new UserId(1)));
        $history = PersonRankHistory::query()->where('person_id', 101)->orderBy('id')->get()->toArray();
        $this->assertNotEmpty($history);
        $person = Person::query()->findOrFail(101);
        $person->current_rank = Rank::CandidateMaster;
        $person->save();
        $oldUpdated = $person->updated;
        RankAuditModel::creating(static function (): never {
            throw new RuntimeException('Simulated audit storage failure');
        });

        try {
            $service->execute(new RebuildPersonRanks(101, new UserId(2)));
            self::fail('Expected audit storage failure');
        } catch (RuntimeException $error) {
            $this->assertSame('Simulated audit storage failure', $error->getMessage());
            $this->assertSame(Rank::CandidateMaster, $person->fresh()->current_rank);
            $this->assertEquals($oldUpdated, $person->fresh()->updated);
            $this->assertSame($history, PersonRankHistory::query()->where('person_id', 101)->orderBy('id')->get()->toArray());
            $this->assertSame(1, RankAuditModel::query()->count());
        } finally {
            RankAuditModel::flushEventListeners();
        }
    }

    #[Test]
    public function calculation_failure_propagates_without_a_business_audit_entry(): void
    {
        $person = Person::factory()->createOne(['current_rank' => Rank::CandidateMaster]);
        $facts = $this->createStub(RankFactsCollector::class);
        $facts->method('collect')->willThrowException(new RuntimeException('Calculation failed'));
        $this->app->instance(RankFactsCollector::class, $facts);

        try {
            $this->app->make(RebuildPersonRanksService::class)->execute(new RebuildPersonRanks($person->id, new UserId(1)));
            self::fail('Expected processing failure');
        } catch (RuntimeException $error) {
            $this->assertSame('Calculation failed', $error->getMessage());
            $this->assertSame(Rank::CandidateMaster, $person->fresh()->current_rank);
            $this->assertSame(0, RankAuditModel::query()->count());
        }
    }
}
