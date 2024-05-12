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

class CupLineSeeder extends Seeder
{
    use WithFaker;

    public function run(): void
    {
        Group::factory(state: ['id' => 101, 'name' => 'М35'])->createOne();
        Group::factory(state: ['id' => 102, 'name' => 'М21E'])->createOne();
        Group::factory(state: ['id' => 103, 'name' => 'М18'])->createOne();
        Group::factory(state: ['id' => 104, 'name' => 'М40'])->createOne();

        Competition::factory(state: ['id' => 101, 'name' => 'Grodno cup', 'from' => '2024-04-12', 'to' => '2024-04-14'])->createOne();

        Event::factory(state: ['id' => 101, 'competition_id' => 101, 'name' => 'Спринт', 'date' => '2024-04-12'])->createOne();

        Distance::factory(state: ['id' => 101, 'group_id' => 101, 'event_id' => 101, 'length' => 2600, 'points' => 26])->createOne();
        Distance::factory(state: ['id' => 102, 'group_id' => 102, 'event_id' => 101, 'length' => 2600, 'points' => 26])->createOne();
        Distance::factory(state: ['id' => 103, 'group_id' => 103, 'event_id' => 101, 'length' => 2600, 'points' => 26])->createOne();
        Distance::factory(state: ['id' => 104, 'group_id' => 104, 'event_id' => 101, 'length' => 2600, 'points' => 26])->createOne();

        Person::factory(state: ['id' => 101, 'lastname' => 'Миссюревич', 'firstname' => 'Алексей', 'birthday' => '2004-01-01'])->createOne();
        Person::factory(state: ['id' => 102, 'lastname' => 'Волчкевич', 'firstname' => 'Ярослав', 'birthday' => '2006-01-01'])->createOne();
        Person::factory(state: ['id' => 103, 'lastname' => 'Воробьев', 'firstname' => 'Дмитрий', 'birthday' => '1980-01-01'])->createOne();
        Person::factory(state: ['id' => 104, 'lastname' => 'Виненко', 'firstname' => 'Александр', 'birthday' => '1981-01-01'])->createOne();

        PersonPayment::factory(state: ['id' => 101, 'person_id' => 101, 'date' => '2024-01-01', 'year' => 2024])->createOne();
        PersonPayment::factory(state: ['id' => 102, 'person_id' => 102, 'date' => '2024-01-12', 'year' => 2024])->createOne();
        PersonPayment::factory(state: ['id' => 103, 'person_id' => 103, 'date' => '2024-01-12', 'year' => 2024])->createOne();
        PersonPayment::factory(state: ['id' => 104, 'person_id' => 104, 'date' => '2024-01-12', 'year' => 2024])->createOne();

        ProtocolLine::factory(state: ['id' => 101, 'lastname' => 'Миссюревич', 'firstname' => 'Алексей', 'club' => 'СК «Камволь»-РЦЭиК', 'year' => '2004', 'rank' => 'МС', 'time' => '00:17:20', 'place' => '1', 'complete_rank' => 'КМС', 'distance_id' => 101, 'person_id' => 101])->createOne();
        ProtocolLine::factory(state: ['id' => 102, 'lastname' => 'Волчкевич', 'firstname' => 'Ярослав', 'club' => 'КСО «Верас»', 'year' => '2006', 'rank' => 'КМС', 'time' => '00:21:23', 'place' => '1', 'distance_id' => 102, 'person_id' => 102])->createOne();
        ProtocolLine::factory(state: ['id' => 103, 'lastname' => 'Воробьев', 'firstname' => 'Дмитрий', 'club' => 'СК «Камволь»', 'year' => '1980', 'rank' => 'I', 'time' => '00:20:53', 'place' => '1', 'distance_id' => 103, 'person_id' => 103])->createOne();
        ProtocolLine::factory(state: ['id' => 104, 'lastname' => 'Виненко', 'firstname' => 'Александр', 'club' => 'КСО «Верас»', 'year' => '1981', 'rank' => 'КМС', 'time' => '00:21:42', 'place' => '2', 'distance_id' => 104, 'person_id' => 104])->createOne();

        Cup::factory(state: ['id' => 101, 'name' => 'Sprint Cup 2024', 'year' => Year::y2024, 'type' => CupType::SPRINT])->createOne();

        CupEvent::factory(state: ['id' => 101, 'cup_id' => 101, 'event_id' => 101, 'points' => 1000])->createOne();
    }
}
