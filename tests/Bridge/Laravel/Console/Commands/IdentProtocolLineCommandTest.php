<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Console\Commands;

use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\Person\Person;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\PersonPrompt\TranslitPersonPromptMetaphone;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use App\Models\IdentLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class IdentProtocolLineCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_activates_a_repeated_master_rank_before_rebuilding_after_queue_identification(): void
    {
        [$person, $oldDistance, $newDistance] = $this->fixtures();
        $preparedLine = 'ivanou-jan-2001';
        ProtocolLine::factory()->createOne([
            'distance_id' => $oldDistance->id,
            'person_id' => $person->id,
            'prepared_line' => $preparedLine,
            'complete_rank' => Rank::MasterOfSport->label(),
            'activate_rank' => '2024-06-01',
        ]);
        /** @var ProtocolLine $line */
        $line = ProtocolLine::factory()->createOne([
            'distance_id' => $newDistance->id,
            'person_id' => null,
            'prepared_line' => $preparedLine,
            'complete_rank' => Rank::MasterOfSport->label(),
            'activate_rank' => null,
        ]);
        PersonPrompt::factory()->createOne([
            'person_id' => $person->id,
            'prompt' => $preparedLine,
            'metaphone' => app(TranslitPersonPromptMetaphone::class)->calculate($preparedLine),
        ]);
        $identLine = new IdentLine();
        $identLine->ident_line = $preparedLine;
        $identLine->save();

        $this->artisan('protocol-lines:queue-ident 1')->assertSuccessful();

        $line->refresh();
        $this->assertSame($person->id, $line->person_id);
        $this->assertSame('2026-06-10', $line->activate_rank?->format('Y-m-d'));
        $person->refresh();
        $this->assertSame(Rank::MasterOfSport, $person->current_rank);
        $this->assertSame('2026-06-10', $person->current_rank_started_on?->format('Y-m-d'));
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
