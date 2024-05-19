<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Competition\Competition;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupType;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Person\Person;
use App\Domain\PersonPayment\PersonPayment;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Models\Year;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;

class ProtocolLinesSeeder extends Seeder
{
    use WithFaker;

    public function run(): void
    {
        Competition::factory(state: ['id' => 1, 'name' => 'test', 'from' => '2021-01-01'])->createOne();
        Competition::factory(state: ['id' => 2, 'name' => 'test2'])->createOne();
        Competition::factory(state: ['id' => 3, 'name' => 'test3', 'from' => '2021-01-01', 'active' => false])->createOne();

        Event::factory(state: ['id' => 101, 'competition_id' => 1, 'name' => 'name1', 'date' => '2022-01-01'])->createOne();
        Event::factory(state: ['id' => 102, 'competition_id' => 1, 'name' => 'name2', 'date' => '2022-03-02'])->createOne();
        Event::factory(state: ['id' => 103, 'competition_id' => 2, 'name' => 'name3', 'description' => 'test3'])->createOne();

        Distance::factory(state: ['id' => 101, 'event_id' => 101])->createOne();
        Distance::factory(state: ['id' => 102, 'event_id' => 101])->createOne();
        Distance::factory(state: ['id' => 103, 'event_id' => 101])->createOne();
        Distance::factory(state: ['id' => 104, 'event_id' => 102])->createOne();

        Person::factory(state: ['id' => 101, 'birthday' => '1987-01-01'])->createOne();
        Person::factory(state: ['id' => 102])->createOne();
        Person::factory(state: ['id' => 103])->createOne();
        Person::factory(state: ['id' => 104])->createOne();
        Person::factory(state: ['id' => 105])->createOne();

        PersonPayment::factory(state: ['id' => 101, 'person_id' => 101, 'date' => '2021-02-12', 'year' => 2021])->createOne();
        PersonPayment::factory(state: ['id' => 102, 'person_id' => 101, 'date' => '2022-01-11', 'year' => 2022])->createOne();
        PersonPayment::factory(state: ['id' => 103, 'person_id' => 101, 'date' => '2023-01-11', 'year' => 2023])->createOne();
        PersonPayment::factory(state: ['id' => 104, 'person_id' => 102, 'date' => '2024-01-11', 'year' => 2024])->createOne();
        PersonPayment::factory(state: ['id' => 105, 'person_id' => 103, 'date' => '2024-01-12', 'year' => 2024])->createOne();
        PersonPayment::factory(state: ['id' => 106, 'person_id' => 104, 'date' => '2024-01-13', 'year' => 2024])->createOne();

        ProtocolLine::factory(state: ['id' => 101, 'distance_id' => 101])->createOne();
        ProtocolLine::factory(state: ['id' => 102, 'distance_id' => 102, 'person_id' => 101])->createOne();
        ProtocolLine::factory(state: ['id' => 103, 'distance_id' => 101, 'person_id' => 102])->createOne();
        ProtocolLine::factory(state: ['id' => 104, 'distance_id' => 101, 'person_id' => 103])->createOne();
        ProtocolLine::factory(state: ['id' => 105, 'distance_id' => 103, 'person_id' => 104])->createOne();
        ProtocolLine::factory(state: ['id' => 106, 'distance_id' => 104, 'person_id' => 101])->createOne();

        Cup::factory(state: ['id' => 101, 'name' => 'test master cup', 'year' => Year::y2022, 'type' => CupType::MASTER, 'events_count' => 3])->createOne();
        Cup::factory(state: ['id' => 102, 'name' => 'test youth cup', 'year' => Year::y2022, 'type' => CupType::YOUTH])->createOne();
        Cup::factory(state: ['id' => 103, 'name' => 'unvisible cup', 'year' => Year::y2022, 'type' => CupType::ELK_PATH, 'visible' => false])->createOne();

        CupEvent::factory(state: ['id' => 101, 'cup_id' => 101, 'event_id' => 102, 'points' => 1001])->createOne();
        CupEvent::factory(state: ['id' => 102, 'cup_id' => 101, 'event_id' => 102, 'points' => 1001, 'active' => false])->createOne();
    }
}
