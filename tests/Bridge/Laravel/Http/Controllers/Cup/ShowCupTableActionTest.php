<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Cup;

use App\Bridge\Laravel\Http\Controllers\Cup\ShowCupTableAction;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ShowCupTableActionTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createApplication();
        RefreshDatabaseState::$migrated = false;
    }

    /**
     * @see ShowCupTableAction::class
     */
    #[Test]
    public function it_shows_cup_table_for_group(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        $this->get('/cups/101/M_35_/table')
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('test master cup', false);
    }

    #[Test]
    public function it_returns_404_for_nonexistent_cup(): void
    {
        $this->get('/cups/9999/M_35_/table')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
