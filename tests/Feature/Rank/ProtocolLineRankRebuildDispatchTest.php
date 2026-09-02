<?php

declare(strict_types=1);

namespace Tests\Feature\Rank;

use App\Bridge\Laravel\Jobs\RebuildPersonRanksJob;
use App\Domain\Auth\Impression;
use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\Person\Person;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Services\PersonPromptService;
use App\Services\ProtocolLineIdentService;
use App\Services\ProtocolLineService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Mav\Slovo\Phonetics;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ProtocolLineRankRebuildDispatchTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_dispatches_one_rank_rebuild_job_for_unique_identified_people(): void
    {
        /** @var Person $person */
        $person = Person::factory()->createOne(['id' => 7]);
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne(['id' => 1]);
        /** @var Event $event */
        $event = Event::factory()->createOne(['id' => 1, 'competition_id' => $competition->id]);
        Group::factory()->createOne(['id' => 1]);
        /** @var Distance $distance */
        $distance = Distance::factory()->createOne(['id' => 1, 'event_id' => $event->id, 'group_id' => 1]);
        $firstLine = ProtocolLine::factory()->createOne(['id' => 1, 'distance_id' => $distance->id, 'person_id' => $person->id]);
        $secondLine = ProtocolLine::factory()->createOne(['id' => 2, 'distance_id' => $distance->id, 'person_id' => $person->id]);
        $protocolLines = $this->createMock(ProtocolLineService::class);
        $protocolLines->expects($this->once())->method('fastIdent');
        $protocolLines->expects($this->once())->method('getProtocolLinesInListWithoutPerson')->willReturn(Collection::empty());
        Queue::fake();

        new ProtocolLineIdentService($protocolLines, new PersonPromptService(), new Phonetics())
            ->identPersons(collect([$firstLine, $secondLine]), new Impression(Carbon::parse('2026-01-01'), 1));

        Queue::assertPushed(RebuildPersonRanksJob::class, static fn(RebuildPersonRanksJob $job): bool => $job->personIds === [7]);
        Queue::assertPushed(RebuildPersonRanksJob::class, 1);
    }
}
