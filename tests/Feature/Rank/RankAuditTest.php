<?php

declare(strict_types=1);

namespace Tests\Feature\Rank;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\RebuildPersonRanks;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Domain\Person\Audit\RankAuditEntry;
use App\Domain\Person\Audit\RankAuditRepository;
use App\Domain\Person\Audit\RankAuditTrigger;
use App\Domain\Person\Audit\RankRebuildFailure;
use App\Domain\Person\Person;
use App\Domain\Person\RankFactsCollector;
use App\Domain\Rank\Rank;
use App\Domain\Shared\Criteria;
use App\Infrastructure\Laravel\Eloquent\Person\RankAudit\RankAuditModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;
use function json_encode;

final class RankAuditTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function repeated_delivery_is_deduplicated_and_a_new_noop_does_not_modify_person(): void
    {
        $person = Person::factory()->createOne(['current_rank' => Rank::FirstRank]);
        $service = $this->app->make(RebuildPersonRanksService::class);
        $command = new RebuildPersonRanks($person->id, new UserId(1), RankAuditTrigger::Manual);
        $service->execute($command);
        $updated = $person->fresh()->updated->at;
        $service->execute($command);
        $this->assertSame(1, RankAuditModel::query()->count());
        $service->execute(new RebuildPersonRanks($person->id, new UserId(2)));
        $this->assertSame(1, RankAuditModel::query()->count());
        $this->assertTrue($person->fresh()->updated->at->equalTo($updated));
        $entry = $this->app->make(RankAuditRepository::class)->byCriteria(new Criteria(['personId' => $person->id]))->items()[0];
        $this->assertSame(RankAuditTrigger::Manual, $entry->trigger);
        $this->assertSame(Rank::FirstRank, $entry->before->current->rank);
        $this->assertSame(Rank::WithoutRank, $entry->after->current->rank);
    }

    #[Test]
    public function failure_to_append_success_rolls_back_projection_before_recording_failure(): void
    {
        $person = Person::factory()->createOne(['current_rank' => Rank::CandidateMaster]);
        $real = $this->app->make(RankAuditRepository::class);
        $audit = $this->createMock(RankAuditRepository::class);
        $audit->method('snapshot')->willReturnCallback($real->snapshot(...));
        $calls = 0;
        $audit->expects($this->exactly(2))->method('add')->willReturnCallback(static function (RankAuditEntry $entry) use (&$calls, $real): void {
            if ($calls++ === 0) {
                throw new RuntimeException('Simulated audit storage failure');
            }
            $real->add($entry);
        });
        $this->app->instance(RankAuditRepository::class, $audit);
        try {
            $this->app->make(RebuildPersonRanksService::class)->execute(new RebuildPersonRanks($person->id, new UserId(1)));
            self::fail('Expected failure');
        } catch (RankRebuildFailure) {
            $this->assertSame(Rank::CandidateMaster, $person->fresh()->current_rank);
            $this->assertNull(RankAuditModel::query()->sole()->after_state);
        }
    }

    #[Test]
    public function failure_preserves_projection_and_records_safe_reason(): void
    {
        $person = Person::factory()->createOne(['current_rank' => Rank::CandidateMaster]);
        $facts = $this->createStub(RankFactsCollector::class);
        $facts->method('collect')->willThrowException(new RuntimeException('private SQL and personal input'));
        $this->app->instance(RankFactsCollector::class, $facts);
        try {
            $this->app->make(RebuildPersonRanksService::class)->execute(new RebuildPersonRanks($person->id, new UserId(1)));
            self::fail('Expected processing failure');
        } catch (RankRebuildFailure) {
            $this->assertSame(Rank::CandidateMaster, $person->fresh()->current_rank);
            $entry = RankAuditModel::query()->sole();
            $this->assertSame(RankAuditTrigger::Manual, $entry->trigger);
            $this->assertNull($entry->after_state);
            $this->assertSame(1, $entry->created->by);
            $this->assertStringNotContainsString('private SQL', (string) json_encode($entry->attributesToArray()));
        }
    }
}
