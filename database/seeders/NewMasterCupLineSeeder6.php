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

class NewMasterCupLineSeeder6 extends Seeder
{
    use WithFaker;

    public function run(): void
    {
        Group::factory(state: ['id' => 101, 'name' => 'М80'])->createOne();
        Group::factory(state: ['id' => 102, 'name' => 'М85'])->createOne();

        Competition::factory(state: ['id' => 101, 'name' => 'Grodno cup', 'from' => '2025-04-12', 'to' => '2025-04-14'])->createOne();

        Event::factory(state: ['id' => 101, 'competition_id' => 101, 'name' => 'Спринт', 'date' => '2025-04-12'])->createOne();

        Distance::factory(state: ['id' => 101, 'group_id' => 101, 'event_id' => 101, 'length' => 2600, 'points' => 27])->createOne();
        Distance::factory(state: ['id' => 102, 'group_id' => 102, 'event_id' => 101, 'length' => 2600, 'points' => 27])->createOne();

        Person::factory(state: ['id' => 101, 'lastname' => 'Триденский', 'firstname' => 'Генадий', 'citizenship' => 'belarus', 'birthday' => '1945-01-01'])->createOne();
        Person::factory(state: ['id' => 102, 'lastname' => 'Карась', 'firstname' => 'Олег', 'citizenship' => 'belarus', 'birthday' => '1940-01-01'])->createOne();

        ProtocolLine::factory(state: ['id' => 101, 'lastname' => 'Триденский', 'firstname' => 'Генадий', 'club' => 'КСО «Верас»', 'year' => '1945', 'rank' => 'МС', 'time' => '00:31:08', 'place' => '1', 'complete_rank' => 'КМС', 'distance_id' => 101, 'person_id' => 101])->createOne();
        ProtocolLine::factory(state: ['id' => 102, 'lastname' => 'Карась', 'firstname' => 'Олег', 'club' => 'КСО «Верас»', 'year' => '1940', 'rank' => 'КМС', 'time' => '00:29:33', 'place' => '1', 'distance_id' => 102, 'person_id' => 102])->createOne();

        PersonPayment::factory(state: ['id' => 101, 'person_id' => 101, 'date' => '2024-01-01', 'year' => 2025])->createOne();
        PersonPayment::factory(state: ['id' => 102, 'person_id' => 102, 'date' => '2024-01-12', 'year' => 2025])->createOne();

        Cup::factory(state: ['id' => 101, 'name' => 'Master Cup 2025', 'year' => Year::y2025, 'type' => CupType::NEW_MASTER])->createOne();

        CupEvent::factory(state: ['id' => 101, 'cup_id' => 101, 'event_id' => 101, 'points' => 1000])->createOne();
    }
}
