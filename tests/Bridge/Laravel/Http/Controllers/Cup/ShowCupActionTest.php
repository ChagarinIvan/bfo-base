<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Cup;

use App\Bridge\Laravel\Http\Controllers\Cup\ShowCupAction;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Symfony\Component\HttpFoundation\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ShowCupActionTest extends TestCase
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
     * @see ShowCupAction::class
     */
    public function it_shows_cup(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        $this->get('/cups/101/show')
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<u class="">test - name2</u>', false)
            ->assertSee('<td>2022-03-02</td>', false)
            ->assertSee('<td>1001</td>', false)
            ->assertSee('<td>1</td>', false)
        ;
    }
}
