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

class YouthCupLineSeeder3 extends Seeder
{
    use WithFaker;

    public function run(): void
    {
        Group::factory(state: ['id' => 101, 'name' => 'M12'])->createOne();
        Group::factory(state: ['id' => 102, 'name' => 'M21T'])->createOne();

        Competition::factory(state: ['id' => 101, 'name' => 'Grodno cup', 'from' => '2025-04-12', 'to' => '2025-04-14'])->createOne();

        Event::factory(state: ['id' => 101, 'competition_id' => 101, 'name' => 'Спринт', 'date' => '2025-04-12'])->createOne();
        Distance::factory(state: ['id' => 101, 'group_id' => 101, 'event_id' => 101, 'length' => 2600, 'points' => 26])->createOne();
        Distance::factory(state: ['id' => 102, 'group_id' => 102, 'event_id' => 101, 'length' => 2700, 'points' => 26])->createOne();

        Person::factory(state: ['id' => 101, 'lastname' => 'Миссюревич', 'firstname' => 'Алексей', 'citizenship' => 'belarus', 'birthday' => '2013-02-02'])->createOne();
        Person::factory(state: ['id' => 102, 'lastname' => 'Волчкевич', 'firstname' => 'Ярослав', 'citizenship' => 'belarus', 'birthday' => '2013-01-01'])->createOne();
        Person::factory(state: ['id' => 103, 'lastname' => 'Балабанов', 'firstname' => 'Александр', 'citizenship' => 'belarus', 'birthday' => '2013-01-01'])->createOne();
        Person::factory(state: ['id' => 104, 'lastname' => 'Мицкевич', 'firstname' => 'Максим', 'citizenship' => 'belarus', 'birthday' => '2005-01-01'])->createOne();

        ProtocolLine::factory(state: ['id' => 101, 'lastname' => 'Миссюревич', 'firstname' => 'Алексей', 'club' => 'СК «Камволь»-РЦЭиК', 'year' => '2013', 'rank' => 'МС', 'time' => '00:08:28', 'place' => '1', 'complete_rank' => 'КМС', 'distance_id' => 101, 'person_id' => 101])->createOne();
        ProtocolLine::factory(state: ['id' => 102, 'lastname' => 'Волчкевич', 'firstname' => 'Ярослав', 'club' => 'КСО «Верас»', 'year' => '2013', 'rank' => 'КМС', 'time' => '00:09:33', 'place' => '2', 'distance_id' => 101, 'person_id' => 102])->createOne();
        ProtocolLine::factory(state: ['id' => 103, 'lastname' => 'Балабанов', 'firstname' => 'Александр', 'club' => 'КСО «Верас»', 'year' => '2013', 'rank' => 'КМС', 'time' => '00:16:47', 'place' => '3', 'distance_id' => 102, 'person_id' => 103])->createOne();
        ProtocolLine::factory(state: ['id' => 104, 'lastname' => 'Мицкевич', 'firstname' => 'Александр', 'club' => 'КСО «Верас»', 'year' => '2005', 'rank' => 'КМС', 'time' => '00:16:30', 'place' => '1', 'distance_id' => 102, 'person_id' => 104])->createOne();

        Cup::factory(state: ['id' => 101, 'name' => 'Youth Cup 2025', 'year' => Year::y2025, 'type' => CupType::NEW_YOUTH])->createOne();

        CupEvent::factory(state: ['id' => 101, 'cup_id' => 101, 'event_id' => 101, 'points' => 1000])->createOne();
    }
}
