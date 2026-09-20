<?php

declare(strict_types=1);

namespace Tests\Feature\Rank;

use App\Domain\Auth\Impression;
use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\Person\Person;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use App\Services\ProtocolLineIdentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RepeatMasterRankActivationTest extends TestCase
{
    use RefreshDatabase;

    /** @return Iterator<string, array{Rank}> */
    public static function repeatMasterRanks(): Iterator
    {
        yield 'KMS' => [Rank::CandidateMaster];
        yield 'MS' => [Rank::MasterOfSport];
    }

    #[Test]
    #[DataProvider('repeatMasterRanks')]
    public function it_activates_a_repeated_master_rank_during_fast_identification(Rank $rank): void
    {
        [$person, $oldDistance, $newDistance] = $this->fixtures();
        ProtocolLine::factory()->createOne([
            'distance_id' => $oldDistance->id,
            'person_id' => $person->id,
            'prepared_line' => 'ivanou-jan-2001',
            'complete_rank' => $rank->label(),
            'activate_rank' => '2024-06-01',
        ]);
        /** @var ProtocolLine $line */
        $line = ProtocolLine::factory()->createOne([
            'distance_id' => $newDistance->id,
            'person_id' => null,
            'prepared_line' => 'ivanou-jan-2001',
            'complete_rank' => $rank->label(),
            'activate_rank' => null,
        ]);
        Queue::fake();

        app(ProtocolLineIdentService::class)->identPersons(
            collect([$line]),
            new Impression(Carbon::parse('2026-06-10'), 1),
        );

        $line->refresh();
        $this->assertSame($person->id, $line->person_id);
        $this->assertSame('2026-06-10', $line->activate_rank?->format('Y-m-d'));
    }

    #[Test]
    public function it_keeps_first_or_unactivated_repeat_master_rank_pending_manual_activation(): void
    {
        [$person, $oldDistance, $newDistance] = $this->fixtures();
        ProtocolLine::factory()->createOne([
            'distance_id' => $oldDistance->id,
            'person_id' => $person->id,
            'prepared_line' => 'ivanou-jan-2001',
            'complete_rank' => Rank::MasterOfSport->label(),
            'activate_rank' => null,
        ]);
        /** @var ProtocolLine $line */
        $line = ProtocolLine::factory()->createOne([
            'distance_id' => $newDistance->id,
            'person_id' => null,
            'prepared_line' => 'ivanou-jan-2001',
            'complete_rank' => Rank::MasterOfSport->label(),
            'activate_rank' => null,
        ]);

        app(ProtocolLineIdentService::class)->identPersons(
            collect([$line]),
            new Impression(Carbon::parse('2026-06-10'), 1),
        );

        $line->refresh();
        $this->assertNull($line->activate_rank);
    }

    /** @return array{Person, Distance, Distance} */
    private function fixtures(): array
    {
        /** @var Person $person */
        $person = Person::factory()->createOne(['id' => 1]);
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne(['id' => 1]);
        /** @var Group $group */
        $group = Group::factory()->createOne(['id' => 1]);
        /** @var Event $oldEvent */
        $oldEvent = Event::factory()->createOne([
            'id' => 1,
            'competition_id' => $competition->id,
            'date' => '2024-06-01',
        ]);
        /** @var Event $newEvent */
        $newEvent = Event::factory()->createOne([
            'id' => 2,
            'competition_id' => $competition->id,
            'date' => '2026-06-10',
        ]);
        /** @var Distance $oldDistance */
        $oldDistance = Distance::factory()->createOne([
            'id' => 1,
            'event_id' => $oldEvent->id,
            'group_id' => $group->id,
        ]);
        /** @var Distance $newDistance */
        $newDistance = Distance::factory()->createOne([
            'id' => 2,
            'event_id' => $newEvent->id,
            'group_id' => $group->id,
        ]);

        return [$person, $oldDistance, $newDistance];
    }
}
