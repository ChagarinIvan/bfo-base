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
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function array_filter;
use function str_contains;
use function strtolower;

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

    #[Test]
    public function it_checks_repeated_master_rank_activation_once_for_the_entire_batch(): void
    {
        [$firstPerson, $oldDistance, $newDistance] = $this->fixtures();
        /** @var Person $secondPerson */
        $secondPerson = Person::factory()->createOne(['id' => 2]);

        foreach ([$firstPerson, $secondPerson] as $person) {
            ProtocolLine::factory()->createOne([
                'distance_id' => $oldDistance->id,
                'person_id' => $person->id,
                'complete_rank' => Rank::MasterOfSport->label(),
                'activate_rank' => '2024-06-01',
            ]);
        }

        /** @var ProtocolLine $firstLine */
        $firstLine = ProtocolLine::factory()->createOne([
            'distance_id' => $newDistance->id,
            'person_id' => $firstPerson->id,
            'complete_rank' => Rank::MasterOfSport->label(),
            'activate_rank' => null,
        ]);
        /** @var ProtocolLine $secondLine */
        $secondLine = ProtocolLine::factory()->createOne([
            'distance_id' => $newDistance->id,
            'person_id' => $secondPerson->id,
            'complete_rank' => Rank::MasterOfSport->label(),
            'activate_rank' => null,
        ]);
        $queries = [];

        DB::listen(static function (QueryExecuted $query) use (&$queries): void {
            $queries[] = strtolower($query->sql);
        });

        app(ProtocolLineIdentService::class)->activateRepeatedMasterRanks(collect([$firstLine, $secondLine]));

        $this->assertCount(1, array_filter(
            $queries,
            static fn (string $query): bool => str_contains($query, 'exists') && str_contains($query, 'protocol_lines'),
        ));
        $this->assertSame('2026-06-10', $firstLine->refresh()->activate_rank?->format('Y-m-d'));
        $this->assertSame('2026-06-10', $secondLine->refresh()->activate_rank?->format('Y-m-d'));
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
