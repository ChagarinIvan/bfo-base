<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Competition;

use App\Bridge\Laravel\Http\Controllers\Competition\ShowCompetitionAction;
use App\Models\Competition;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Symfony\Component\HttpFoundation\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ShowCompetitionActionTest extends TestCase
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
     * @see ShowCompetitionAction::class
     */
    public function it_shows_competition(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        $this->get("/competitions/1/show")
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<a href="http://localhost/events/1/d/1">name1</a>', false)
            ->assertSee('<a href="http://localhost/events/2/d/0">name2</a>', false)
            ->assertDontSee('<a href="http://localhost/events/3/d/0">name3</a>', false)
        ;
    }
}
