<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Cup;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Cup\ClearCupCacheAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Cup\DeleteCupAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Cup\DeleteCupEventAction;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Infrastructure\Sanctum\SanctumUser;
use Database\Seeders\SprintCupLineSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see ClearCupCacheAction
 * @see DeleteCupAction
 * @see DeleteCupEventAction
 */
final class CupMaintenanceActionsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_rejects_guests_and_missing_resources(): void
    {
        $this->deleteJson('/api/v1/cups/101')->assertUnauthorized();
        $this->deleteJson('/api/v1/cup-events/101')->assertUnauthorized();
        $this->postJson('/api/v1/cups/cache-clear')->assertUnauthorized();

        $this->authenticate();
        $this->deleteJson('/api/v1/cups/999999')->assertNotFound();
        $this->deleteJson('/api/v1/cup-events/999999')->assertNotFound();
        $this->postJson('/api/v1/cups/999999/cache-clear')->assertNotFound();
    }

    #[Test]
    public function it_retains_the_spa_root_and_removes_old_action_urls(): void
    {
        $this->get('/')->assertRedirect('/app/competitions');
        $this->get('/cups/101/cache')->assertNotFound();
        $this->postJson('/api/v1/cups/101/cache-clear')->assertNotFound();
        $this->get('/cups/101/delete')->assertNotFound();
        $this->get('/cups/101/export')->assertNotFound();
        $this->get('/cups/101/M_0_/table-export')->assertNotFound();
        $this->get('/cups/101/7/delete')->assertNotFound();
    }

    #[Test]
    public function it_clears_all_cup_caches_without_a_cup_id(): void
    {
        $this->seed(SprintCupLineSeeder::class);
        $this->authenticate();
        Cache::tags(['cups'])->put('table_101_M_0_', 'stale', 60);
        Cache::tags(['cups'])->put('table_202_M_0_', 'stale', 60);
        Cup::query()->whereKey(101)->update(['active' => false]);

        $this->postJson('/api/v1/cups/cache-clear')->assertNoContent();

        $this->assertNull(Cache::tags(['cups'])->get('table_101_M_0_'));
        $this->assertNull(Cache::tags(['cups'])->get('table_202_M_0_'));
    }

    #[Test]
    public function it_disables_a_stage_and_then_the_cup(): void
    {
        $this->seed(SprintCupLineSeeder::class);
        $this->authenticate();

        $this->deleteJson('/api/v1/cup-events/101')->assertNoContent();
        $this->assertFalse(CupEvent::query()->findOrFail(101)->active);

        $this->deleteJson('/api/v1/cups/101')->assertNoContent();
        $this->assertFalse(Cup::query()->findOrFail(101)->active);
    }

    private function authenticate(): void
    {
        Sanctum::actingAs(SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]));
    }
}
