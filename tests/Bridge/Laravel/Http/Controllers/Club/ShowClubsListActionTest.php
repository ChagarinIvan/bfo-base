<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Club;

use App\Bridge\Laravel\Http\Controllers\Club\ShowClubsListAction;
use App\Domain\Club\Club;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Symfony\Component\HttpFoundation\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ShowClubsListActionTest extends TestCase
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
     * @see ShowClubsListAction::class
     */
    public function it_shows_clubs(): void
    {
        Club::factory(state: ['name' => 'test1'])->createOne();
        Club::factory(state: ['name' => 'test2'])->createOne();

        $this->get("/clubs")
            ->assertStatus(Response::HTTP_OK)
            ->assertSeeTextInOrder([
                'test1',
                'test2',
            ])
        ;
    }
}
