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

final class RefillPersonRanksTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_rebuilds_idempotently_from_identified_protocol_lines_only(): void
    {
        /** @var Person $person */
        $person = Person::factory()->createOne(['id' => 1, 'birthday' => '2000-01-01']);
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne(['id' => 1, 'mass' => false]);
        /** @var Event $event */
        $event = Event::factory()->createOne(['id' => 1, 'competition_id' => $competition->id, 'date' => '2026-01-10']);
        Group::factory()->createOne(['id' => 1, 'name' => 'M21']);
        /** @var Distance $distance */
        $distance = Distance::factory()->createOne(['id' => 1, 'event_id' => $event->id, 'group_id' => 1]);
        ProtocolLine::factory()->createOne([
            'id' => 1,
            'distance_id' => $distance->id,
            'person_id' => $person->id,
            'complete_rank' => 'I',
            'activate_rank' => null,
        ]);
        ProtocolLine::factory()->createOne([
            'id' => 2,
            'distance_id' => $distance->id,
            'person_id' => null,
            'complete_rank' => 'МСМК',
            'activate_rank' => null,
        ]);

        $this->rebuild($person->id);

        $person->refresh();
        $this->assertSame(Rank::FirstRank, $person->current_rank);
        self::assertDatabaseCount('person_rank_histories', 1);
        self::assertDatabaseHas('person_rank_histories', [
            'person_id' => $person->id,
            'protocol_line_id' => 1,
            'distance_id' => $distance->id,
            'event_id' => $event->id,
            'competition_id' => $competition->id,
            'rank' => Rank::FirstRank->value,
        ]);
        $firstHistory = PersonRankHistory::query()->sole()->getRawOriginal();
        unset($firstHistory['id']);

        $this->rebuild($person->id);

        self::assertDatabaseCount('person_rank_histories', 1);
        $secondHistory = PersonRankHistory::query()->sole()->getRawOriginal();
        unset($secondHistory['id']);
        $this->assertSame($firstHistory, $secondHistory);
    }

    private function rebuild(int $personId): void
    {
        app(RebuildPersonRanksService::class)->execute(new RebuildPersonRanks($personId, new UserId(1)));
    }
}
