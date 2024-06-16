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

class JuniorCupLineSeeder2 extends Seeder
{
    use WithFaker;

    public function run(): void
    {
        Group::factory(state: ['id' => 101, 'name' => 'M20'])->createOne();
        Group::factory(state: ['id' => 102, 'name' => 'М21Е'])->createOne();
        Group::factory(state: ['id' => 103, 'name' => 'M21А'])->createOne();
        Group::factory(state: ['id' => 104, 'name' => 'M18'])->createOne();

        Competition::factory(state: ['id' => 101, 'name' => 'Grodno cup', 'from' => '2024-04-12', 'to' => '2024-04-14'])->createOne();

        Event::factory(state: ['id' => 101, 'competition_id' => 101, 'name' => 'Спринт', 'date' => '2024-04-12'])->createOne();

        Distance::factory(state: ['id' => 102, 'group_id' => 102, 'event_id' => 101, 'length' => 2700, 'points' => 26])->createOne();
        Distance::factory(state: ['id' => 103, 'group_id' => 103, 'event_id' => 101, 'length' => 2700, 'points' => 26])->createOne();
        Distance::factory(state: ['id' => 104, 'group_id' => 104, 'event_id' => 101, 'length' => 2700, 'points' => 26])->createOne();

        Person::factory(state: ['id' => 101, 'lastname' => 'Миссюревич', 'firstname' => 'Алексей', 'birthday' => '2006-01-01'])->createOne();
        Person::factory(state: ['id' => 102, 'lastname' => 'Волчкевич', 'firstname' => 'Ярослав', 'birthday' => '2007-01-01'])->createOne();
        Person::factory(state: ['id' => 103, 'lastname' => 'Test1', 'firstname' => 'Test1', 'birthday' => '2004-01-01'])->createOne();
        Person::factory(state: ['id' => 104, 'lastname' => 'Test2', 'firstname' => 'Test2', 'birthday' => '2003-01-01'])->createOne();
        Person::factory(state: ['id' => 105, 'lastname' => 'Test3', 'firstname' => 'Test3', 'birthday' => '2005-01-01'])->createOne();
        Person::factory(state: ['id' => 106, 'lastname' => 'Test4', 'firstname' => 'Test4', 'birthday' => '2009-01-01'])->createOne();

        PersonPayment::factory(state: ['id' => 101, 'person_id' => 101, 'date' => '2024-01-01', 'year' => 2024])->createOne();
        PersonPayment::factory(state: ['id' => 102, 'person_id' => 102, 'date' => '2024-01-12', 'year' => 2024])->createOne();
        PersonPayment::factory(state: ['id' => 103, 'person_id' => 103, 'date' => '2024-01-12', 'year' => 2024])->createOne();
        PersonPayment::factory(state: ['id' => 104, 'person_id' => 104, 'date' => '2024-01-12', 'year' => 2024])->createOne();
        PersonPayment::factory(state: ['id' => 105, 'person_id' => 105, 'date' => '2024-01-12', 'year' => 2024])->createOne();
        PersonPayment::factory(state: ['id' => 106, 'person_id' => 106, 'date' => '2024-01-12', 'year' => 2024])->createOne();

        ProtocolLine::factory(state: ['id' => 101, 'lastname' => 'Миссюревич', 'firstname' => 'Алексей', 'year' => '2006', 'rank' => 'МС', 'time' => '00:17:20', 'distance_id' => 102, 'person_id' => 101])->createOne();
        ProtocolLine::factory(state: ['id' => 102, 'lastname' => 'Волчкевич', 'firstname' => 'Ярослав', 'year' => '2007', 'rank' => 'КМС', 'time' => '00:21:23', 'distance_id' => 102, 'person_id' => 102])->createOne();
        ProtocolLine::factory(state: ['id' => 103, 'lastname' => 'Test1', 'firstname' => 'Test1', 'year' => '2004', 'rank' => 'КМС', 'time' => '00:27:42', 'distance_id' => 103, 'person_id' => 103])->createOne();
        ProtocolLine::factory(state: ['id' => 104, 'lastname' => 'Test2', 'firstname' => 'Test2', 'year' => '2003', 'rank' => 'КМС', 'time' => '00:23:42', 'distance_id' => 103, 'person_id' => 104])->createOne();
        ProtocolLine::factory(state: ['id' => 105, 'lastname' => 'Test3', 'firstname' => 'Test3', 'year' => '2005', 'rank' => 'КМС', 'time' => '00:24:42', 'distance_id' => 104, 'person_id' => 105])->createOne();
        ProtocolLine::factory(state: ['id' => 106, 'lastname' => 'Test3', 'firstname' => 'Test3', 'year' => '2009', 'rank' => 'КМС', 'time' => '00:19:42', 'distance_id' => 104, 'person_id' => 106])->createOne();

        Cup::factory(state: ['id' => 101, 'name' => 'Junior Cup 2024', 'year' => Year::y2024, 'type' => CupType::JUNIORS])->createOne();

        CupEvent::factory(state: ['id' => 101, 'cup_id' => 101, 'event_id' => 101, 'points' => 1000])->createOne();
    }
}
