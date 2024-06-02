<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\CupEvents;

use App\Bridge\Laravel\Http\Controllers\Cup\ShowCupEventGroupAction;
use App\Domain\Cup\CupType\SprintCupType;
use App\Domain\Cup\CupType\YouthCupType;
use App\Domain\User\User;
use Database\Seeders\SprintCupLineSeeder;
use Database\Seeders\YouthCupLineSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Symfony\Component\HttpFoundation\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ShowCupEventGroupActionTest extends TestCase
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
     * @see ShowCupEventGroupAction::class
     * @see SprintCupType::class
     */
    public function it_shows_sprint_cup_event_group_action(): void
    {
        $this->seed(SprintCupLineSeeder::class);

        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get('/cups/101/101/M_0_/show')
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<h2 id="up">Sprint Cup 2024 - 2024</h2>', false)
            ->assertSee('<a href="http://localhost/competitions/101/show">Grodno cup</a>', false)
            ->assertSee('<a href="http://localhost/events/d/101">Спринт - 2024-04-12</a>', false)
            ->assertSee('<a href="http://localhost/cups/101/101/M_0_/show" class="text-decoration-none nav-link active">', false)
            ->assertSee('<a href="http://localhost/cups/101/101/W_0_/show" class="text-decoration-none nav-link ">', false)
            ->assertSee('<a href="http://localhost/persons/101/show">Миссюревич Алексей</a>', false)
            ->assertSee('<td><b class="text-info">1000</b></td>', false)
            ->assertSee('<a href="http://localhost/persons/103/show">Воробьев Дмитрий</a>', false)
            ->assertSee('<td>660</td>', false)
            ->assertSee('<a href="http://localhost/persons/102/show">Волчкевич Ярослав</a>', false)
            ->assertSee('<td>621</td>', false)
            ->assertSee('<a href="http://localhost/persons/104/show">Виненко Александр</a>', false)
            ->assertSee('<td>598</td>', false)
        ;
    }

    /**
     * @test
     * @see ShowCupEventGroupAction::class
     * @see YouthCupType::class
     */
    public function it_shows_youth_cup_event_group_action(): void
    {
        $this->seed(YouthCupLineSeeder::class);

        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get('/cups/101/101/M_18_/show')
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<h2 id="up">Youth Cup 2024 - 2024</h2>', false)
            ->assertSee('<a href="http://localhost/competitions/101/show">Grodno cup</a>', false)
            ->assertSee('<a href="http://localhost/events/d/101">Спринт - 2024-04-12</a>', false)
            ->assertSee('<a href="http://localhost/cups/101/101/M_12_/show" class="text-decoration-none nav-link ">', false)
            ->assertSee('<a href="http://localhost/persons/102/show">Волчкевич Ярослав</a>', false)
            ->assertSee('<td>900</td>', false)
            ->assertDontSee('Миссюревич', false)
            ->assertSee('<a href="http://localhost/persons/103/show">Виненко Александр</a>', false)
            ->assertSee('<td>880</td>', false)
        ;
    }
}
