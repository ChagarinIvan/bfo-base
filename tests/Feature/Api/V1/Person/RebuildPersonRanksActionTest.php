<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Person;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Person\RebuildPersonRanksAction;
use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\Person\Person;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use App\Domain\Shared\Clock;
use App\Domain\Shared\FrozenClock;
use App\Infrastructure\Sanctum\SanctumUser;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** @see RebuildPersonRanksAction */

final class RebuildPersonRanksActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_requires_authentication(): void
    {
        $person = Person::factory()->createOne();

        $this->postJson("/api/v1/persons/{$person->getKey()}/ranks/rebuild")
            ->assertUnauthorized();
    }

    #[Test]
    public function it_rebuilds_one_person(): void
    {
        Sanctum::actingAs($this->createUser());
        $person = Person::factory()->createOne();

        $this->postJson("/api/v1/persons/{$person->getKey()}/ranks/rebuild")
            ->assertNoContent();
    }

    #[Test]
    public function it_awards_junior_third_rank_after_three_eligible_finishes_without_a_recorded_rank(): void
    {
        $this->app->instance(Clock::class, new FrozenClock(Carbon::parse('2026-07-01')));
        Sanctum::actingAs($this->createUser());
        $person = Person::factory()->createOne(['birthday' => '2012-01-01']);
        $group = Group::factory()->createOne();
        $competition = Competition::factory()->createOne();
        $massCompetition = Competition::factory()->createOne(['mass' => true]);
        $inactiveCompetition = Competition::factory()->createOne(['active' => false]);
        $url = "/api/v1/persons/{$person->id}/ranks/rebuild";

        $this->start($person, $group, $competition, '2025-12-20');
        $this->start($person, $group, $massCompetition, '2026-01-01');
        $this->start($person, $group, $inactiveCompetition, '2026-01-02');
        $this->start($person, $group, $competition, '2026-01-03', ['vk' => true]);
        $this->start($person, $group, $competition, '2026-01-04', ['time' => null]);
        $this->start($person, $group, $competition, '2026-02-01');
        $this->start($person, $group, $competition, '2026-03-01', ['complete_rank' => 'б/р']);

        $this->postJson($url)->assertNoContent();
        $this->assertSame(Rank::WithoutRank, $person->refresh()->current_rank);
        $this->assertDatabaseCount('person_rank_histories', 0);

        $thirdFinish = $this->start($person, $group, $competition, '2026-04-01', ['complete_rank' => '-']);
        $this->postJson($url)->assertNoContent();
        $this->postJson($url)->assertNoContent();
        $this->assertSame(Rank::JuniorThirdRank, $person->refresh()->current_rank);
        $this->assertDatabaseCount('person_rank_histories', 1);
        $this->assertDatabaseHas('person_rank_histories', [
            'person_id' => $person->id,
            'protocol_line_id' => $thirdFinish->id,
            'rank' => Rank::JuniorThirdRank->value,
            'achieved_on' => '2026-04-01',
            'activated_on' => '2026-04-01',
        ]);
    }

    #[Test]
    public function it_returns_not_found_for_a_missing_person(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->postJson('/api/v1/persons/999999/ranks/rebuild')
            ->assertNotFound();
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => 'secret',
        ]);
    }

    /** @param array<string, mixed> $attributes */
    private function start(Person $person, Group $group, Competition $competition, string $date, array $attributes = []): ProtocolLine
    {
        $event = Event::factory()->createOne([
            'competition_id' => $competition->id,
            'date' => $date,
            'file' => 'results.html',
        ]);
        $distance = Distance::factory()->createOne(['event_id' => $event->id, 'group_id' => $group->id]);

        return ProtocolLine::factory()->createOne([
            'distance_id' => $distance->id,
            'person_id' => $person->id,
            'complete_rank' => '',
            'activate_rank' => null,
            ...$attributes,
        ]);
    }
}
