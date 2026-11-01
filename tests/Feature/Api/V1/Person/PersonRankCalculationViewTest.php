<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Person;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Person\ListPersonsAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Person\RebuildPersonRanksAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Person\ViewPersonAction;
use App\Domain\Person\Person;
use App\Domain\Person\RankCalculationReason;
use App\Domain\Shared\Clock;
use App\Domain\Shared\FrozenClock;
use App\Infrastructure\Sanctum\SanctumUser;
use Carbon\Carbon;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see ViewPersonAction
 * @see ListPersonsAction
 * @see RebuildPersonRanksAction
 */
final class PersonRankCalculationViewTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authenticated_person_views_include_nullable_calculation_metadata_beside_rank(): void
    {
        Sanctum::actingAs(SanctumUser::query()->create(['email' => 'rank@example.test', 'password' => 'secret']));
        $person = Person::factory()->createOne();
        $this->getJson("/api/v1/persons/{$person->id}")->assertOk()
            ->assertJsonStructure(['rankId', 'rankCalculatedAt', 'rankCalculationReason'])
            ->assertJsonPath('rankCalculatedAt', null)->assertJsonPath('rankCalculationReason', null);
        $now = Carbon::parse('2026-11-01 12:34:56');
        $this->app->instance(Clock::class, new FrozenClock($now));
        $this->postJson("/api/v1/persons/{$person->id}/ranks/rebuild", ['reason' => 'daily'])->assertNoContent();
        $this->getJson("/api/v1/persons/{$person->id}")->assertOk()
            ->assertJsonPath('rankId', $person->fresh()->current_rank->value)
            ->assertJsonPath('rankCalculatedAt', $now->format(DateTimeImmutable::ATOM))
            ->assertJsonPath('rankCalculationReason', 'manual');
        $this->getJson('/api/v1/persons')->assertOk()
            ->assertJsonPath('0.rankCalculatedAt', $now->format(DateTimeImmutable::ATOM))
            ->assertJsonPath('0.rankCalculationReason', 'manual');
    }

    #[Test]
    public function guest_person_views_never_include_calculation_metadata(): void
    {
        $person = Person::factory()->createOne(['rank_calculated_at' => Carbon::parse('2026-11-01'), 'rank_calculation_reason' => RankCalculationReason::Protocol]);
        $this->getJson("/api/v1/persons/{$person->id}")->assertOk()->assertJsonStructure(['rankId'])
            ->assertJsonMissingPath('rankCalculatedAt')->assertJsonMissingPath('rankCalculationReason');
        $this->getJson('/api/v1/persons')->assertOk()->assertJsonStructure([['rankId']])
            ->assertJsonMissingPath('0.rankCalculatedAt')->assertJsonMissingPath('0.rankCalculationReason');
    }
}
