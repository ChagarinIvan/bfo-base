<?php

declare(strict_types=1);

namespace Tests\Feature\Rank;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\RebuildPersonRanks;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRankHistory;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FirstRankActivationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_new_first_rank_waits_for_activation_during_rebuild(): void
    {
        [$person, $distance] = $this->personAndDistance();
        $line = ProtocolLine::factory()->createOne([
            'distance_id' => $distance->id,
            'person_id' => $person->id,
            'complete_rank' => Rank::FirstRank->label(),
            'activate_rank' => null,
        ]);

        $this->rebuild($person->id);

        $this->assertSame(Rank::WithoutRank, $person->fresh()->current_rank);
        $this->assertDatabaseHas('person_rank_histories', [
            'protocol_line_id' => $line->id,
            'rank' => Rank::FirstRank->value,
            'activated_on' => null,
            'finished_on' => null,
        ]);
    }

    #[Test]
    public function an_existing_activated_first_rank_remains_active_without_activating_a_new_result(): void
    {
        [$person, $distance] = $this->personAndDistance();
        $oldLine = ProtocolLine::factory()->createOne([
            'distance_id' => $distance->id,
            'person_id' => $person->id,
            'complete_rank' => Rank::FirstRank->label(),
            'activate_rank' => '2026-01-10',
        ]);
        $laterEvent = Event::factory()->createOne([
            'competition_id' => $distance->event->competition_id,
            'date' => '2026-03-10',
        ]);
        $laterDistance = Distance::factory()->createOne([
            'event_id' => $laterEvent->id,
            'group_id' => $distance->group_id,
        ]);
        $pendingLine = ProtocolLine::factory()->createOne([
            'distance_id' => $laterDistance->id,
            'person_id' => $person->id,
            'complete_rank' => Rank::FirstRank->label(),
            'activate_rank' => null,
        ]);

        $this->rebuild($person->id);
        $this->rebuild($person->id);

        $this->assertSame(Rank::FirstRank, $person->fresh()->current_rank);
        $this->assertSame('2026-01-10', $oldLine->fresh()->activate_rank?->toDateString());
        $this->assertNull($pendingLine->fresh()->activate_rank);
        $this->assertSame('2026-01-10', PersonRankHistory::query()->where('protocol_line_id', $oldLine->id)->sole()->activated_on?->toDateString());
        $this->assertSame('2028-01-10', PersonRankHistory::query()->where('protocol_line_id', $oldLine->id)->sole()->finished_on?->toDateString());
        $this->assertNull(PersonRankHistory::query()->where('protocol_line_id', $pendingLine->id)->sole()->activated_on);
    }

    /** @return array{Person, Distance} */
    private function personAndDistance(): array
    {
        $person = Person::factory()->createOne(['birthday' => '2000-01-01']);
        $competition = Competition::factory()->createOne(['mass' => false]);
        $event = Event::factory()->createOne(['competition_id' => $competition->id, 'date' => '2026-01-10']);
        $group = Group::factory()->createOne();
        $distance = Distance::factory()->createOne(['event_id' => $event->id, 'group_id' => $group->id]);

        return [$person, $distance];
    }

    private function rebuild(int $personId): void
    {
        $this->app->make(RebuildPersonRanksService::class)->execute(new RebuildPersonRanks($personId, new UserId(1)));
    }
}
