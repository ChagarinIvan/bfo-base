<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Competition\Competition;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupType;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\Person\Person;
use App\Domain\PersonPayment\PersonPayment;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Models\Year;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;

class NewMasterCupLineSeeder3 extends Seeder
{
    use WithFaker;

    public function run(): void
    {
        Group::factory(state: ['id' => 101, 'name' => 'Ж60'])->createOne();
        Group::factory(state: ['id' => 102, 'name' => 'Ж65'])->createOne();
        Group::factory(state: ['id' => 103, 'name' => 'Ж70'])->createOne();

        Competition::factory(state: ['id' => 101, 'name' => 'Grodno cup', 'from' => '2025-04-12', 'to' => '2025-04-14'])->createOne();

        Event::factory(state: ['id' => 101, 'competition_id' => 101, 'name' => 'Спринт', 'date' => '2025-04-12'])->createOne();

        // эквивалентные М60 и М70
        Distance::factory(state: ['id' => 101, 'group_id' => 101, 'event_id' => 101, 'length' => 2600, 'points' => 27])->createOne();
        Distance::factory(state: ['id' => 102, 'group_id' => 103, 'event_id' => 101, 'length' => 2600, 'points' => 27])->createOne();

        Person::factory(state: ['id' => 101, 'lastname' => 'Варыгина', 'firstname' => 'Светлана', 'citizenship' => 'belarus', 'birthday' => '1960-01-01'])->createOne();
        Person::factory(state: ['id' => 102, 'lastname' => 'Конон', 'firstname' => 'Ирина', 'citizenship' => 'belarus', 'birthday' => '1956-01-01'])->createOne();
        Person::factory(state: ['id' => 103, 'lastname' => 'Авраменко', 'firstname' => 'Лариса', 'citizenship' => 'belarus', 'birthday' => '1955-01-01'])->createOne();

        ProtocolLine::factory(state: ['id' => 101, 'lastname' => 'Варыгина', 'firstname' => 'Светлана', 'club' => 'КСО «Верас»', 'year' => '1960', 'rank' => 'МС', 'time' => '00:26:51', 'place' => '1', 'complete_rank' => 'КМС', 'distance_id' => 101, 'person_id' => 101])->createOne();
        ProtocolLine::factory(state: ['id' => 102, 'lastname' => 'Конон', 'firstname' => 'Ирина', 'club' => 'КСО «Верас»', 'year' => '1956', 'rank' => 'КМС', 'time' => '00:30:56', 'place' => '2', 'distance_id' => 101, 'person_id' => 102])->createOne();
        ProtocolLine::factory(state: ['id' => 103, 'lastname' => 'Авраменко', 'firstname' => 'Лариса', 'club' => 'КСО «Верас»', 'year' => '1955', 'rank' => 'КМС', 'time' => '00:30:49', 'place' => '1', 'distance_id' => 102, 'person_id' => 103])->createOne();

        PersonPayment::factory(state: ['id' => 101, 'person_id' => 101, 'date' => '2024-01-01', 'year' => 2025])->createOne();
        PersonPayment::factory(state: ['id' => 102, 'person_id' => 102, 'date' => '2024-01-12', 'year' => 2025])->createOne();
        PersonPayment::factory(state: ['id' => 103, 'person_id' => 103, 'date' => '2024-01-12', 'year' => 2025])->createOne();

        Cup::factory(state: ['id' => 101, 'name' => 'Master Cup 2025', 'year' => Year::y2025, 'type' => CupType::NEW_MASTER])->createOne();

        CupEvent::factory(state: ['id' => 101, 'cup_id' => 101, 'event_id' => 101, 'points' => 1000])->createOne();
    }
}
