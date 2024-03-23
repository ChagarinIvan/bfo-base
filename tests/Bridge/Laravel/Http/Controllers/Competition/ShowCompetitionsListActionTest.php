<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Competition;

use App\Bridge\Laravel\Http\Controllers\Competition\ShowCompetitionsListAction;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Symfony\Component\HttpFoundation\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ShowCompetitionsListActionTest extends TestCase
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
     * @see ShowCompetitionsListAction::class
     */
    public function it_shows_competitions(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        $this->get("/competitions?year=2021")
            ->assertStatus(Response::HTTP_OK)
            ->assertSeeTextInOrder([
                'test',
                '2021-01-01',
            ])
            ->assertDontSee(['test2', 'test3'])
        ;
    }
}
