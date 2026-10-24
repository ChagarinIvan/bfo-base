<?php

declare(strict_types=1);

namespace Tests\Feature\Rank;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\CorrectPersonRanks;
use App\Application\Service\Person\CorrectPersonRanksService;
use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\Person\Audit\RankAuditTrigger;
use App\Domain\Person\Person;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use App\Infrastructure\Laravel\Eloquent\Person\RankAudit\RankAuditModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function iterator_to_array;

final class CorrectPersonRanksTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function preview_apply_and_repeat_preserve_manual_dates_and_audit_values(): void
    {
        $person = Person::factory()->createOne(['birthday' => '2000-01-01']);
        $competition = Competition::factory()->createOne(['mass' => false]);
        $group = Group::factory()->createOne();
        $lines = [];
        foreach (['2025-01-01' => ['КМС', '2025-01-01'], '2026-01-01' => ['I', null]] as $date => [$rank, $activation]) {
            $event = Event::factory()->createOne(['competition_id' => $competition->id, 'date' => $date]);
            $distance = Distance::factory()->createOne(['event_id' => $event->id, 'group_id' => $group->id]);
            $lines[] = ProtocolLine::factory()->createOne(['person_id' => $person->id, 'distance_id' => $distance->id, 'complete_rank' => $rank, 'activate_rank' => $activation]);
        }
        $this->assertDatabaseCount('person_rank_histories', 0);
        $auditCount = RankAuditModel::query()->count();
        $service = $this->app->make(CorrectPersonRanksService::class);
        $preview = iterator_to_array($service->execute(new CorrectPersonRanks(new UserId(1), $person->id)));
        $this->assertCount(1, $preview);
        $this->assertSame($auditCount, RankAuditModel::query()->count());
        $applied = iterator_to_array($service->execute(new CorrectPersonRanks(new UserId(1), $person->id, true)));
        $this->assertSame($preview[0]['after'], $applied[0]['after']);
        $this->assertNull($lines[1]->fresh()->activate_rank);
        $this->assertDatabaseHas('person_rank_histories', ['protocol_line_id' => $lines[1]->id, 'activated_on' => '2026-01-01 00:00:00']);
        $this->assertSame('2025-01-01', $lines[0]->fresh()->activate_rank?->toDateString());
        $this->assertSame(Rank::CandidateMaster, $person->fresh()->current_rank);
        $snapshot = RankAuditModel::query()->orderByDesc('id')->first()->after_state;
        $this->assertSame(RankAuditTrigger::Correction, RankAuditModel::query()->orderByDesc('id')->first()->trigger);
        $this->assertSame([], iterator_to_array($service->execute(new CorrectPersonRanks(new UserId(1), $person->id, true))));
        $lines[0]->delete();
        $this->assertSame($snapshot, RankAuditModel::query()->orderByDesc('id')->first()->after_state);
    }
}
