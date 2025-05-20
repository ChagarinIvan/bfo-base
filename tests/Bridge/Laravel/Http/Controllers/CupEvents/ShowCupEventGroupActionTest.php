<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\CupEvents;

use App\Bridge\Laravel\Http\Controllers\Cup\ShowCupEventGroupAction;
use App\Domain\Cup\CupType\JuniorCupType;
use App\Domain\Cup\CupType\NewMasterCupType;
use App\Domain\Cup\CupType\SprintCupType;
use App\Domain\Cup\CupType\YouthCupType;
use App\Domain\User\User;
use Database\Seeders\JuniorCupLineSeeder;
use Database\Seeders\JuniorCupLineSeeder2;
use Database\Seeders\NewMasterCupLineSeeder;
use Database\Seeders\SprintCupLineSeeder;
use Database\Seeders\YouthCupLine2Seeder;
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

    /**
     * @test
     * @see ShowCupEventGroupAction::class
     * @see YouthCupType::class
     */
    public function it_shows_youth_cup_event_group_action_2(): void
    {
        $this->seed(YouthCupLine2Seeder::class);

        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get('/cups/101/101/W_16_/show')
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<h2 id="up">Youth Cup 2024 - 2024</h2>', false)
            ->assertSee('<a href="http://localhost/competitions/101/show">Grodno cup</a>', false)
            ->assertSee('<a href="http://localhost/events/d/101">Спринт - 2024-04-12</a>', false)
            ->assertSee('<a href="http://localhost/cups/101/101/M_12_/show" class="text-decoration-none nav-link ">', false)
            ->assertSee('<a href="http://localhost/persons/103/show">Колядко Полина</a>', false)
            ->assertSee('<td>850</td>', false)
            ->assertDontSee('Журомская', false)
            ->assertSee('<a href="http://localhost/persons/102/show">Холод Ирина</a>', false)
            ->assertSee('<td>781</td>', false)
        ;
    }

    /**
     * @test
     * @see ShowCupEventGroupAction::class
     * @see JuniorCupType::class
     */
    public function it_shows_junior_cup_event_group_action(): void
    {
        $this->seed(JuniorCupLineSeeder::class);

        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get('/cups/101/101/M_20_/show')
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<h2 id="up">Junior Cup 2024 - 2024</h2>', false)
            ->assertSee('<a href="http://localhost/competitions/101/show">Grodno cup</a>', false)
            ->assertSee('<a href="http://localhost/events/d/101">Спринт - 2024-04-12</a>', false)
            ->assertSee('<a href="http://localhost/cups/101/101/M_20_/show" class="text-decoration-none nav-link active">', false)
            ->assertSee('<a href="http://localhost/persons/101/show">Миссюревич Алексей</a>', false)
            ->assertSee('<td><b class="text-info">1000</b></td>', false)
            ->assertDontSee('Волчкевич', false)
            ->assertDontSee('Test2', false)
            ->assertDontSee('Test4', false)
            ->assertSee('<a href="http://localhost/persons/105/show">Test3 Test3</a>', false)
            ->assertSee('<td>404</td>', false)
            ->assertSee('<a href="http://localhost/persons/103/show">Test1 Test1</a>', false)
            ->assertSee('<td>252</td>', false)
        ;
    }

    /**
     * @test
     * @see ShowCupEventGroupAction::class
     * @see JuniorCupType::class
     */
    public function it_shows_junior_cup_event_group_action2(): void
    {
        $this->seed(JuniorCupLineSeeder2::class);

        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get('/cups/101/101/M_20_/show')
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<h2 id="up">Junior Cup 2024 - 2024</h2>', false)
            ->assertSee('<a href="http://localhost/competitions/101/show">Grodno cup</a>', false)
            ->assertSee('<a href="http://localhost/events/d/102">Спринт - 2024-04-12</a>', false)
            ->assertSee('<a href="http://localhost/cups/101/101/M_20_/show" class="text-decoration-none nav-link active">', false)
            ->assertSee('<a href="http://localhost/persons/101/show">Миссюревич Алексей</a>', false)
            ->assertSee('<td><b class="text-info">1000</b></td>', false)
            ->assertSee('<a href="http://localhost/persons/102/show">Волчкевич Ярослав</a>', false)
            ->assertSee('<td>621</td>', false)
            ->assertDontSee('Test2', false)
            ->assertDontSee('Test4', false)
            ->assertSee('<a href="http://localhost/persons/105/show">Test3 Test3</a>', false)
            ->assertSee('<td>404</td>', false)
            ->assertSee('<a href="http://localhost/persons/103/show">Test1 Test1</a>', false)
            ->assertSee('<td>252</td>', false)
        ;
    }

    /**
     * @test
     * @see ShowCupEventGroupAction::class
     * @see NewMasterCupType::class
     */
    public function it_shows_new_master_cup_event_group_action(): void
    {
        $this->seed(NewMasterCupLineSeeder::class);

        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get('/cups/101/101/M_55_/show')
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<h2 id="up">Master Cup 2024 - 2024</h2>', false)
            ->assertSee('<a href="http://localhost/competitions/101/show">Grodno cup</a>', false)
            ->assertSee('<a href="http://localhost/events/d/101">Спринт - 2024-04-12</a>', false)
            ->assertSee('<a href="http://localhost/cups/101/101/M_35_/show" class="text-decoration-none nav-link ">', false)
            ->assertSee('<a href="http://localhost/cups/101/101/M_55_/show" class="text-decoration-none nav-link active">', false)
            ->assertSee('<a href="http://localhost/persons/103/show">Сияльский Владислав</a>', false)
            ->assertSee('<td><b class="text-info">1000</b></td>', false)
        ;
    }

    /**
     * @test
     * @see ShowCupEventGroupAction::class
     * @see NewMasterCupType::class
     */
    public function it_shows_new_master_cup_event_group_action2(): void
    {
        $this->seed(NewMasterCupLineSeeder::class);

        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get('/cups/101/101/M_60_/show')
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<h2 id="up">Master Cup 2024 - 2024</h2>', false)
            ->assertSee('<a href="http://localhost/competitions/101/show">Grodno cup</a>', false)
            ->assertSee('<a href="http://localhost/events/d/101">Спринт - 2024-04-12</a>', false)
            ->assertSee('<a href="http://localhost/cups/101/101/M_35_/show" class="text-decoration-none nav-link ">', false)
            ->assertSee('<a href="http://localhost/cups/101/101/M_60_/show" class="text-decoration-none nav-link active">', false)
            ->assertDontSee('<a href="http://localhost/persons/101/show">Триденский Генадий</a>', false)
            ->assertSee('<a href="http://localhost/persons/102/show">Макаревич Иосиф</a>', false)
            ->assertSee('<td>891</td>', false)
        ;
    }

    /**
     * @test
     * @see ShowCupEventGroupAction::class
     * @see NewMasterCupType::class
     */
    public function it_shows_new_master_cup_event_group_action3(): void
    {
        $this->seed(NewMasterCupLineSeeder::class);

        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get('/cups/101/101/M_65_/show')
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<h2 id="up">Master Cup 2024 - 2024</h2>', false)
            ->assertSee('<a href="http://localhost/competitions/101/show">Grodno cup</a>', false)
            ->assertSee('<a href="http://localhost/events/d/101">Спринт - 2024-04-12</a>', false)
            ->assertSee('<a href="http://localhost/cups/101/101/M_35_/show" class="text-decoration-none nav-link ">', false)
            ->assertSee('<a href="http://localhost/cups/101/101/M_65_/show" class="text-decoration-none nav-link active">', false)
            ->assertSee('<a href="http://localhost/persons/104/show">Колядко Иван</a>', false)
            ->assertSee('<td><b class="text-info">1000</b></td>', false)
            ->assertSee('<a href="http://localhost/persons/101/show">Триденский Генадий</a>', false)
            ->assertSee('<td><b class="text-info">1000</b></td>', false)
        ;
    }
}
