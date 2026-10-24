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
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RankActivationTest extends TestCase
{
    use RefreshDatabase;

    /** @return iterable<string, array{Rank, Rank, string}> */
    public static function confirmations(): iterable
    {
        yield 'I after KMS' => [Rank::CandidateMaster, Rank::FirstRank, '2025-01-01'];
        yield 'KMS after activated MS' => [Rank::MasterOfSport, Rank::CandidateMaster, '2025-01-01'];
        yield 'KMS after expired KMS' => [Rank::CandidateMaster, Rank::CandidateMaster, '2020-01-01'];
        yield 'MS after expired MS' => [Rank::MasterOfSport, Rank::MasterOfSport, '2020-01-01'];
        yield 'MS after activated MSMK' => [Rank::WorldClassMaster, Rank::MasterOfSport, '2025-01-01'];
        yield 'MSMK after expired MSMK' => [Rank::WorldClassMaster, Rank::WorldClassMaster, '2020-01-01'];
    }
    /** @return iterable<string, array{string}> */
    public static function invalidSources(): iterable
    {
        foreach (['inactive_event', 'inactive_competition', 'mass', 'vk', 'no_time', 'other_person'] as $reason) {
            yield $reason => [$reason];
        }
    }

    #[Test]
    #[DataProvider('confirmations')]
    public function equal_or_higher_confirmation_activates_the_result_without_a_protocol_flag_and_withdrawal_removes_the_basis(Rank $basisRank, Rank $targetRank, string $basisDate): void
    {
        $person = Person::factory()->createOne(['id' => null, 'birthday' => '2000-01-01']);
        $competition = Competition::factory()->createOne(['id' => null, 'mass' => false]);
        $group = Group::factory()->createOne();
        $lines = [];
        foreach ([$basisDate => [$basisRank->label(), $basisDate], '2026-01-01' => [$targetRank->label(), null]] as $date => [$rank, $activation]) {
            $event = Event::factory()->createOne(['competition_id' => $competition->id, 'date' => $date]);
            $distance = Distance::factory()->createOne(['event_id' => $event->id, 'group_id' => $group->id]);
            $lines[] = ProtocolLine::factory()->createOne([
                'person_id' => $person->id, 'distance_id' => $distance->id,
                'complete_rank' => $rank, 'activate_rank' => $activation,
            ]);
        }
        $rebuild = $this->app->make(RebuildPersonRanksService::class);
        $rebuild->execute(new RebuildPersonRanks($person->id, new UserId(1), on: Carbon::parse('2026-07-01')));
        $this->assertSame($basisDate === '2020-01-01' ? $targetRank : $basisRank, $person->fresh()->current_rank);
        $this->assertSame('2026-01-01', PersonRankHistory::query()->where('protocol_line_id', $lines[1]->id)->sole()->activated_on?->toDateString());
        $lines[0]->activate_rank = null;
        $lines[0]->save();
        $rebuild->execute(new RebuildPersonRanks($person->id, new UserId(1), on: Carbon::parse('2026-07-01')));
        $this->assertNull(PersonRankHistory::query()->where('protocol_line_id', $lines[1]->id)->sole()->activated_on);
        $lines[1]->activate_rank = Carbon::parse('2026-02-01');
        $lines[1]->save();
        $rebuild->execute(new RebuildPersonRanks($person->id, new UserId(1), on: Carbon::parse('2026-07-01')));
        $this->assertDatabaseHas('person_rank_histories', ['protocol_line_id' => $lines[1]->id, 'activated_on' => '2026-02-01 00:00:00']);
    }

    #[Test]
    #[DataProvider('invalidSources')]
    public function an_ineligible_or_foreign_confirmation_cannot_activate_a_rank(string $reason): void
    {
        $person = Person::factory()->createOne(['id' => null, 'birthday' => '2000-01-01']);
        $group = Group::factory()->createOne();
        $lines = [];
        foreach (['2020-01-01', '2026-01-01'] as $date) {
            $competition = Competition::factory()->createOne(['id' => null, 'mass' => false]);
            $event = Event::factory()->createOne(['competition_id' => $competition->id, 'date' => $date]);
            $distance = Distance::factory()->createOne(['event_id' => $event->id, 'group_id' => $group->id]);
            $lines[] = ProtocolLine::factory()->createOne([
                'person_id' => $person->id, 'distance_id' => $distance->id, 'complete_rank' => 'КМС',
                'activate_rank' => $date === '2020-01-01' ? $date : null,
            ]);
        }
        $source = $lines[0];
        if ($reason === 'inactive_event') {
            $source->distance->event->active = false;
            $source->distance->event->save();
        } elseif ($reason === 'inactive_competition') {
            $source->distance->event->competition->active = false;
            $source->distance->event->competition->save();
        } elseif ($reason === 'mass') {
            $source->distance->event->competition->mass = true;
            $source->distance->event->competition->save();
        } elseif ($reason === 'vk') {
            $source->vk = true;
            $source->save();
        } elseif ($reason === 'no_time') {
            $source->time = null;
            $source->save();
        } else {
            $source->person_id = Person::factory()->createOne(['id' => null])->id;
            $source->save();
        }
        $this->app->make(RebuildPersonRanksService::class)->execute(new RebuildPersonRanks($person->id, new UserId(1), on: Carbon::parse('2026-07-01')));
        $this->assertNull(PersonRankHistory::query()->where('protocol_line_id', $lines[1]->id)->sole()->activated_on);
        $this->assertNull($lines[1]->fresh()->activate_rank);
    }
}
