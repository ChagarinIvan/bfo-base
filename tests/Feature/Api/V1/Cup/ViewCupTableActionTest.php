<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Cup;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Cup\ViewCupTableAction;
use Database\Seeders\SprintCupLineSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** @see ViewCupTableAction */
final class ViewCupTableActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_a_cup_table(): void
    {
        $this->seed(SprintCupLineSeeder::class);

        $this->getJson('/api/v1/cups/101/tables/M_0_')
            ->assertOk()
            ->assertJsonStructure(['stages', 'rows'])
        ;
    }
}
