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

class YouthCupLineSeeder5 extends Seeder
{
    use WithFaker;

    public function run(): void
    {
        Group::factory(state: ['id' => 101, 'name' => 'M18'])->createOne();
        Group::factory(state: ['id' => 102, 'name' => 'M20'])->createOne();
        Group::factory(state: ['id' => 103, 'name' => 'M21'])->createOne();

        Competition::factory(state: ['id' => 101, 'name' => 'Grodno cup', 'from' => '2025-04-12', 'to' => '2025-04-14'])->createOne();

        Event::factory(state: ['id' => 101, 'competition_id' => 101, 'name' => 'Спринт', 'date' => '2025-04-12'])->createOne();

        Distance::factory(state: ['id' => 101, 'group_id' => 101, 'event_id' => 101, 'length' => 2600, 'points' => 26])->createOne();
        Distance::factory(state: ['id' => 102, 'group_id' => 102, 'event_id' => 101, 'length' => 2600, 'points' => 26])->createOne();
        Distance::factory(state: ['id' => 103, 'group_id' => 103, 'event_id' => 101, 'length' => 2600, 'points' => 26])->createOne();

        Person::factory(state: ['id' => 101, 'lastname' => 'Михалкин', 'firstname' => 'Игорь', 'citizenship' => 'belarus', 'birthday' => '2008-01-01'])->createOne();
        Person::factory(state: ['id' => 102, 'lastname' => 'Ильин', 'firstname' => 'Роман', 'citizenship' => 'belarus', 'birthday' => '2007-01-01'])->createOne();
        Person::factory(state: ['id' => 103, 'lastname' => 'Лабкович', 'firstname' => 'Иван', 'citizenship' => 'belarus', 'birthday' => '2008-01-01'])->createOne();
        Person::factory(state: ['id' => 104, 'lastname' => 'Марков', 'firstname' => 'Виталий', 'citizenship' => 'belarus', 'birthday' => '1993-01-01'])->createOne();

        ProtocolLine::factory(state: ['id' => 101, 'lastname' => 'Михалкин', 'firstname' => 'Игорь', 'club' => 'КСО «Верас»', 'year' => '2008', 'rank' => 'МС', 'time' => '00:18:34', 'place' => '1', 'complete_rank' => 'КМС', 'distance_id' => 101, 'person_id' => 101])->createOne();
        ProtocolLine::factory(state: ['id' => 102, 'lastname' => 'Ильин', 'firstname' => 'Роман', 'club' => 'КСО «Верас»', 'year' => '2007', 'rank' => 'КМС', 'time' => '00:21:13', 'place' => '1', 'distance_id' => 102, 'person_id' => 102])->createOne();
        ProtocolLine::factory(state: ['id' => 103, 'lastname' => 'Лабкович', 'firstname' => 'Иван', 'club' => 'КСО «Верас»', 'year' => '2007', 'rank' => 'КМС', 'time' => '00:22:04', 'place' => '13', 'distance_id' => 103, 'person_id' => 103])->createOne();
        ProtocolLine::factory(state: ['id' => 104, 'lastname' => 'Марков', 'firstname' => 'Виталий', 'club' => 'КСО «Верас»', 'year' => '1993', 'rank' => 'КМС', 'time' => '00:18:22', 'place' => '1', 'distance_id' => 103, 'person_id' => 104])->createOne();

        Cup::factory(state: ['id' => 101, 'name' => 'Youth Cup 2025', 'year' => Year::y2025, 'type' => CupType::NEW_YOUTH])->createOne();

        CupEvent::factory(state: ['id' => 101, 'cup_id' => 101, 'event_id' => 101, 'points' => 1000])->createOne();
    }
}
