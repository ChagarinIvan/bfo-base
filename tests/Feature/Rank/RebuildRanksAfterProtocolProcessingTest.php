<?php

declare(strict_types=1);

namespace Tests\Feature\Rank;

use App\Application\Service\Rank\RebuildPersonRanks;
use App\Application\Service\Rank\RebuildPersonRanksService;
use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\Person\Person;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RebuildRanksAfterProtocolProcessingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_updates_both_people_when_an_identified_protocol_line_is_reassigned_or_unlinked(): void
    {
        /** @var Person $firstPerson */
        $firstPerson = Person::factory()->createOne(['id' => 1]);
        /** @var Person $secondPerson */
        $secondPerson = Person::factory()->createOne(['id' => 2]);
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne(['id' => 1, 'mass' => false]);
        /** @var Event $event */
        $event = Event::factory()->createOne(['id' => 1, 'competition_id' => $competition->id, 'date' => '2026-01-10']);
        Group::factory()->createOne(['id' => 1]);
        /** @var Distance $distance */
        $distance = Distance::factory()->createOne(['id' => 1, 'event_id' => $event->id, 'group_id' => 1]);
        /** @var ProtocolLine $line */
        $line = ProtocolLine::factory()->createOne([
            'id' => 1,
            'distance_id' => $distance->id,
            'person_id' => $firstPerson->id,
            'complete_rank' => 'I',
            'activate_rank' => null,
        ]);

        $this->rebuild($firstPerson->id);
        $firstPerson->refresh();
        $this->assertSame(Rank::FirstRank, $firstPerson->current_rank);

        $line->update(['person_id' => $secondPerson->id]);
        $this->rebuild($firstPerson->id);
        $this->rebuild($secondPerson->id);

        $firstPerson->refresh();
        $secondPerson->refresh();
        $this->assertSame(Rank::WithoutRank, $firstPerson->current_rank);
        $this->assertSame(Rank::FirstRank, $secondPerson->current_rank);

        $line->update(['person_id' => null]);
        $this->rebuild($secondPerson->id);

        $secondPerson->refresh();
        $this->assertSame(Rank::WithoutRank, $secondPerson->current_rank);
        self::assertDatabaseCount('person_rank_histories', 0);
    }

    private function rebuild(int $personId): void
    {
        app(RebuildPersonRanksService::class)->execute(new RebuildPersonRanks($personId));
    }
}
