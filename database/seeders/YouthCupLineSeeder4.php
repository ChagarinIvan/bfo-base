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

class YouthCupLineSeeder4 extends Seeder
{
    use WithFaker;

    public function run(): void
    {
        Group::factory(state: ['id' => 101, 'name' => 'M20'])->createOne();

        Competition::factory(state: ['id' => 101, 'name' => 'Grodno cup', 'from' => '2025-04-12', 'to' => '2025-04-14'])->createOne();

        Event::factory(state: ['id' => 101, 'competition_id' => 101, 'name' => 'Спринт', 'date' => '2025-04-12'])->createOne();

        Distance::factory(state: ['id' => 101, 'group_id' => 101, 'event_id' => 101, 'length' => 2600, 'points' => 26])->createOne();

        Person::factory(state: ['id' => 101, 'lastname' => 'Журомская', 'firstname' => 'Вероника', 'citizenship' => 'belarus', 'birthday' => '2008-01-01'])->createOne();
        Person::factory(state: ['id' => 102, 'lastname' => 'Холод', 'firstname' => 'Ирина', 'citizenship' => 'belarus', 'birthday' => '2008-01-01'])->createOne();
        Person::factory(state: ['id' => 103, 'lastname' => 'Колядко', 'firstname' => 'Полина', 'citizenship' => 'belarus', 'birthday' => '2005-01-01'])->createOne();

        ProtocolLine::factory(state: ['id' => 101, 'lastname' => 'Журомская', 'firstname' => 'Вероника', 'club' => 'КСО «Верас»', 'year' => '2008', 'rank' => 'МС', 'time' => '00:18:48', 'place' => '1', 'complete_rank' => 'КМС', 'distance_id' => 101, 'person_id' => 101])->createOne();
        ProtocolLine::factory(state: ['id' => 102, 'lastname' => 'Холод', 'firstname' => 'Ирина', 'club' => 'КСО «Верас»', 'year' => '2008', 'rank' => 'КМС', 'time' => '00:19:53', 'place' => '1', 'distance_id' => 101, 'person_id' => 102])->createOne();
        ProtocolLine::factory(state: ['id' => 103, 'lastname' => 'Колядко', 'firstname' => 'Полина', 'club' => 'КСО «Верас»', 'year' => '2005', 'rank' => 'КМС', 'time' => '00:15:10', 'place' => '2', 'distance_id' => 101, 'person_id' => 103])->createOne();

        Cup::factory(state: ['id' => 101, 'name' => 'Youth Cup 2025', 'year' => Year::y2025, 'type' => CupType::NEW_YOUTH])->createOne();

        CupEvent::factory(state: ['id' => 101, 'cup_id' => 101, 'event_id' => 101, 'points' => 1000])->createOne();
    }
}
