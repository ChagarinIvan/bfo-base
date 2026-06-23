<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Console\Commands;

use App\Domain\Person\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FixInactivePersonsProtocolLinesCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createApplication();
        RefreshDatabaseState::$migrated = false;
    }

    #[Test]
    public function it_sets_person_id_to_null_for_inactive_persons(): void
    {
        // Создаем неактивных персон
        Person::factory()->state(['id' => 1, 'active' => false])->createOne();
        Person::factory()->state(['id' => 2, 'active' => false])->createOne();
        
        // Создаем активную персону
        Person::factory()->state(['id' => 3, 'active' => true])->createOne();

        // Находим существующие protocol_lines или создадим тестовые
        // Обновляем существующие protocol_lines напрямую для теста
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        DB::table('protocol_lines')->insert([
            'id' => 999101,
            'serial_number' => 1,
            'person_id' => 1,
            'lastname' => 'Test1',
            'firstname' => 'User1',
            'club' => 'TestClub',
            'year' => 2000,
            'rank' => 'ii',
            'runner_number' => 101,
            'time' => '00:16:23',
            'place' => 1,
            'complete_rank' => 'i',
            'points' => 100,
            'distance_id' => 1,
            'prepared_line' => 'Test',
            'vk' => false,
            'activate_rank' => '2020-01-01',
        ]);
        
        DB::table('protocol_lines')->insert([
            'id' => 999102,
            'serial_number' => 2,
            'person_id' => 2,
            'lastname' => 'Test2',
            'firstname' => 'User2',
            'club' => 'TestClub',
            'year' => 2000,
            'rank' => 'ii',
            'runner_number' => 102,
            'time' => '00:16:23',
            'place' => 1,
            'complete_rank' => 'i',
            'points' => 100,
            'distance_id' => 1,
            'prepared_line' => 'Test',
            'vk' => false,
            'activate_rank' => '2020-01-01',
        ]);
        
        DB::table('protocol_lines')->insert([
            'id' => 999201,
            'serial_number' => 3,
            'person_id' => 3,
            'lastname' => 'Active',
            'firstname' => 'User',
            'club' => 'TestClub',
            'year' => 2000,
            'rank' => 'ii',
            'runner_number' => 201,
            'time' => '00:16:23',
            'place' => 1,
            'complete_rank' => 'i',
            'points' => 100,
            'distance_id' => 1,
            'prepared_line' => 'Test',
            'vk' => false,
            'activate_rank' => '2020-01-01',
        ]);
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Проверяем что все связи существуют
        $this->assertDatabaseHas('protocol_lines', ['id' => 999101, 'person_id' => 1]);
        $this->assertDatabaseHas('protocol_lines', ['id' => 999102, 'person_id' => 2]);
        $this->assertDatabaseHas('protocol_lines', ['id' => 999201, 'person_id' => 3]);
        
        // Debug: проверяем что персоны существуют и неактивны
        $person1Data = DB::table('person')->where('id', 1)->first();
        $person2Data = DB::table('person')->where('id', 2)->first();
        $this->assertNotNull($person1Data, 'Person 1 should exist');
        $this->assertEquals(0, $person1Data->active, 'Person 1 should be inactive (0)');
        $this->assertEquals(0, $person2Data->active, 'Person 2 should be inactive (0)');
        
        // Debug: проверяем что запрос находит записи
        $count = DB::table('protocol_lines')
            ->join('person', 'person.id', '=', 'protocol_lines.person_id')
            ->where('person.active', false)
            ->whereNotNull('protocol_lines.person_id')
            ->count();
        $this->assertGreaterThan(0, $count, "Query should find protocol lines, found: {$count}");

        // Запускаем команду с автоподтверждением (--no-interaction)
        $this->artisan('protocol-lines:fix-inactive-persons')
            ->expectsConfirmation('Do you want to proceed with setting person_id to null?', 'yes')
            ->assertExitCode(0);

        // Проверяем что person_id для неактивных персон стал null
        $line1 = DB::table('protocol_lines')->where('id', 999101)->first();
        $line2 = DB::table('protocol_lines')->where('id', 999102)->first();
        $line3 = DB::table('protocol_lines')->where('id', 999201)->first();
        
        $this->assertNull($line1->person_id, 'Protocol line 999101 person_id should be null');
        $this->assertNull($line2->person_id, 'Protocol line 999102 person_id should be null');
        $this->assertEquals(3, $line3->person_id, 'Protocol line 999201 person_id should remain 3');
        
        // Cleanup
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('protocol_lines')->whereIn('id', [999101, 999102, 999201])->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    #[Test]
    public function it_handles_no_protocol_lines_case(): void
    {
        // Запускаем команду когда нет protocol_line для неактивных персон
        $this->artisan('protocol-lines:fix-inactive-persons', ['--no-interaction' => true]);

        // Test passes if no exception thrown
        $this->assertTrue(true);
    }
}
