<?php

declare(strict_types=1);
namespace Tests\Feature\Person;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Person\ManualRank\AddManualPersonRankAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Person\ManualRank\DeleteManualPersonRankAction;
use App\Domain\Person\ManualRank\Event\ManualPersonRankAdded;
use App\Domain\Person\ManualRank\Event\ManualPersonRankDisabled;
use App\Domain\Person\ManualRank\ManualPersonRankRepository;
use App\Domain\Person\Person;
use App\Domain\Shared\Criteria;
use App\Infrastructure\Sanctum\SanctumUser;
use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Connection;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
/**
 * @see AddManualPersonRankAction
 * @see DeleteManualPersonRankAction
 */
final class ManualPersonRankEventsTest extends TestCase
{
    use DatabaseTruncation;

    #[Test]
    public function source_events_run_once_after_real_commit_with_projection_already_saved(): void
    {
        $this->beforeApplicationDestroyed(fn () => $this->truncateDatabaseTables());
        $this->travelTo(Carbon::parse('2026-11-08'));
        Sanctum::actingAs(SanctumUser::query()->create(['email' => 'manual-rank-events@example.test', 'password' => 'unused']));
        $person = Person::factory()->createOne(['id' => 1001]);
        $db = $this->app->make(Connection::class);
        $seen = [];
        $events = $this->app->make(Dispatcher::class);
        $events->listen(ManualPersonRankAdded::class, function ($event) use ($db, &$seen): void {
            $this->assertSame(0, $db->transactionLevel());
            $this->assertDatabaseHas('person_rank_histories', ['manual_rank_id' => $event->manualRankId]);
            $seen[] = 'added';
        });
        $events->listen(ManualPersonRankDisabled::class, function () use ($db, &$seen): void {
            $this->assertSame(0, $db->transactionLevel());
            $this->assertDatabaseCount('person_rank_histories', 0);
            $seen[] = 'disabled';
        });
        $url = "/api/v1/persons/{$person->id}/manual-ranks";
        $id = $this->postJson($url, ['rankId' => 6, 'achievedOn' => '2026-01-01', 'activatedOn' => '2026-01-01'])->assertCreated()->json('id');
        $this->assertSame(['added'], $seen);
        $repository = $this->app->make(ManualPersonRankRepository::class);
        $source = $repository->oneByCriteria(new Criteria(['id' => $id]));
        $this->assertNotNull($source);
        $db->transaction(static fn () => $repository->update($source));
        $this->assertSame(['added'], $seen);
        $this->deleteJson("{$url}/{$id}")->assertNoContent();
        $this->assertSame(['added', 'disabled'], $seen);
    }
}
