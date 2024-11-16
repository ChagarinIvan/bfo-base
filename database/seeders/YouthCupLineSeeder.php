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

class YouthCupLineSeeder extends Seeder
{
    use WithFaker;

    public function run(): void
    {
        Group::factory(state: ['id' => 101, 'name' => 'M18'])->createOne();

        Competition::factory(state: ['id' => 101, 'name' => 'Grodno cup', 'from' => '2024-04-12', 'to' => '2024-04-14'])->createOne();

        Event::factory(state: ['id' => 101, 'competition_id' => 101, 'name' => 'Спринт', 'date' => '2024-04-12'])->createOne();

        Distance::factory(state: ['id' => 101, 'group_id' => 101, 'event_id' => 101, 'length' => 2600, 'points' => 26])->createOne();

        Person::factory(state: ['id' => 101, 'lastname' => 'Миссюревич', 'firstname' => 'Алексей', 'citizenship' => 'other', 'birthday' => '2006-02-02'])->createOne();
        Person::factory(state: ['id' => 102, 'lastname' => 'Волчкевич', 'firstname' => 'Ярослав', 'citizenship' => 'belarus', 'birthday' => '2006-01-01'])->createOne();
        Person::factory(state: ['id' => 103, 'lastname' => 'Виненко', 'firstname' => 'Александр', 'citizenship' => 'belarus', 'birthday' => '2007-01-01'])->createOne();

        ProtocolLine::factory(state: ['id' => 101, 'lastname' => 'Миссюревич', 'firstname' => 'Алексей', 'club' => 'СК «Камволь»-РЦЭиК', 'year' => '2004', 'rank' => 'МС', 'time' => '00:17:20', 'place' => '1', 'complete_rank' => 'КМС', 'distance_id' => 101, 'person_id' => 101])->createOne();
        ProtocolLine::factory(state: ['id' => 102, 'lastname' => 'Волчкевич', 'firstname' => 'Ярослав', 'club' => 'КСО «Верас»', 'year' => '2006', 'rank' => 'КМС', 'time' => '00:21:23', 'place' => '1', 'distance_id' => 101, 'person_id' => 102])->createOne();
        ProtocolLine::factory(state: ['id' => 103, 'lastname' => 'Виненко', 'firstname' => 'Александр', 'club' => 'КСО «Верас»', 'year' => '1981', 'rank' => 'КМС', 'time' => '00:21:42', 'place' => '2', 'distance_id' => 101, 'person_id' => 103])->createOne();

        Cup::factory(state: ['id' => 101, 'name' => 'Youth Cup 2024', 'year' => Year::y2024, 'type' => CupType::YOUTH])->createOne();

        CupEvent::factory(state: ['id' => 101, 'cup_id' => 101, 'event_id' => 101, 'points' => 1])->createOne();
    }
}
