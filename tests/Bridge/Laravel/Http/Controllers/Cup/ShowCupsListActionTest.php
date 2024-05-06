<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Cup;

use App\Bridge\Laravel\Http\Controllers\Cup\ShowCupsListAction;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Symfony\Component\HttpFoundation\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ShowCupsListActionTest extends TestCase
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
     * @test
     * @see ShowCupsListAction::class
     */
    public function it_shows_cups(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        $this->get('/cups?year=2022&visible=1')
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<a href="http://localhost/cups/101/show">test master cup</a>', false)
            ->assertSee('<a href="http://localhost/cups/101/M_35_/table" class="text-decoration-none">', false)
            ->assertSee('<a href="http://localhost/cups/102/show">test youth cup</a>', false)
            ->assertDontSee('<a href="http://localhost/cups/103/show">unvisible cup</a>', false)
            ->assertSee('<a href="http://localhost/cups/102/M_12_/table" class="text-decoration-none">', false)
        ;
    }
}
