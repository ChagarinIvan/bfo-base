<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Person;

use App\Bridge\Laravel\Http\Controllers\Person\ShowPersonAction;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ShowPersonActionTest extends TestCase
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
     * @see ShowPersonAction::class
     */
    public function it_shows_person(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        $this->get('/persons/101/show')
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<h4>Последняя оплата: 2023-01-11</h4>', false)
            ->assertSee('<td class="text-center" colspan="9"><b id="2022">2022</b></td>', false)
            ->assertSee('<a href="http://localhost/competitions/1/show">', false)
            ->assertSee('<a href="http://localhost/events/d/104#106">', false)
            ->assertSee('<td>2022-03-02</td>', false)
            ->assertSee('<td>M21</td>', false)
            ->assertSee('<td>00:16:23</td>', false)
        ;
    }
}
