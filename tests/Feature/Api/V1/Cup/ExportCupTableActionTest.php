<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Cup;

use App\Bridge\Laravel\Http\Controllers\Cup\ExportCupTableAction;
use App\Infrastructure\Sanctum\SanctumUser;
use Database\Seeders\SprintCupLineSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** @see ExportCupTableAction */
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
        ;
    }
}
