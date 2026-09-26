<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Cup;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Cup\ExportCupTableAction;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Cup\Group\GroupMale;
use App\Infrastructure\Sanctum\SanctumUser;
use Database\Seeders\SprintCupLineSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function count;

/**
 * @see ExportCupTableAction
 */
final class ExportCupTableActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_requires_authentication_and_returns_the_csv_export(): void
    {
        $this->get('/api/v1/cups/101/export')->assertUnauthorized();

        $this->seed(SprintCupLineSeeder::class);
        Sanctum::actingAs(SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]));

        $this->get('/api/v1/cups/101/export')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertHeader('Content-Disposition')
            ->assertSeeText('Место;ФИО;Год;Клуб;Очки')
            ->assertSeeText('Миссюревич Алексей')
            ->assertSeeText(new CupGroup(GroupMale::Man)->name())
            ->assertSeeText(new CupGroup(GroupMale::Woman)->name())
        ;
    }

    #[Test]
    public function it_exports_rows_from_the_same_table_as_the_json_view(): void
    {
        $this->seed(SprintCupLineSeeder::class);
        Sanctum::actingAs(SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]));

        $table = $this->getJson('/api/v1/cups/101/tables/M_0_')->assertOk()->json();
        $export = $this->get('/api/v1/cups/101/export')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertHeader('Content-Disposition');

        foreach ($table as $row) {
            $export->assertSeeText($row['personName']);
            $export->assertSeeText($row['totalPoints']);
        }
    }

    #[Test]
    public function it_rejects_invalid_export_requests_as_json(): void
    {
        $this->getJson('/api/v1/cups/101/export')->assertUnauthorized();
        $this->seed(SprintCupLineSeeder::class);
        Sanctum::actingAs(SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]));

        $this->getJson('/api/v1/cups/999999/export')->assertNotFound()->assertJsonPath('errors.0.code', 'cup_not_found');
        $this->getJson('/api/v1/cups/101/tables/M_0_/export')->assertNotFound();
    }

    #[Test]
    public function it_exports_headings_for_an_empty_cup(): void
    {
        $this->seed(SprintCupLineSeeder::class);
        CupEvent::query()->where('cup_id', 101)->update(['active' => false]);
        Sanctum::actingAs(SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]));

        $this->get('/api/v1/cups/101/export')
            ->assertOk()
            ->assertSeeText('Место;ФИО;Год;Клуб;Очки')
            ->assertDontSeeText('Миссюревич Алексей');
    }

    #[Test]
    public function full_export_reuses_cached_tables(): void
    {
        $this->seed(SprintCupLineSeeder::class);
        Sanctum::actingAs(SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]));

        Cache::tags(['cups', 101])->flush();
        DB::enableQueryLog();
        $this->get('/api/v1/cups/101/export')->assertOk();
        $coldQueries = count(DB::getQueryLog());
        DB::flushQueryLog();
        $this->get('/api/v1/cups/101/export')->assertOk();
        $warmQueries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThan($coldQueries, $warmQueries);
    }
}
