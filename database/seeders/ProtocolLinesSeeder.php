<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Competition;
use App\Models\Distance;
use App\Models\Event;
use App\Models\Person;
use App\Models\PersonPayment;
use App\Models\ProtocolLine;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;

class ProtocolLinesSeeder extends Seeder
{
    use WithFaker;

    public function run(): void
    {
        $competition = Competition::factory()->createOne();
        $event = Event::factory(state: ['competition_id' => $competition->id])->createOne();
        Distance::factory(state: ['id' => 1, 'event_id' => $event->id])->createOne();
        Distance::factory(state: ['id' => 2, 'event_id' => $event->id])->createOne();
        Distance::factory(state: ['id' => 3, 'event_id' => $event->id])->createOne();

        Person::factory(state: ['id' => 1])->createOne();
        Person::factory(state: ['id' => 2])->createOne();
        Person::factory(state: ['id' => 3])->createOne();
        Person::factory(state: ['id' => 4])->createOne();

        PersonPayment::factory(state: ['id' => 1, 'person_id' => 1, 'date' => '2021-02-12', 'year' => 2021])->createOne();
        PersonPayment::factory(state: ['id' => 2, 'person_id' => 1, 'date' => '2022-03-13', 'year' => 2022])->createOne();
        PersonPayment::factory(state: ['id' => 3, 'person_id' => 1, 'date' => '2023-01-11', 'year' => 2023])->createOne();
        PersonPayment::factory(state: ['id' => 4, 'person_id' => 2, 'date' => '2024-01-11', 'year' => 2024])->createOne();
        PersonPayment::factory(state: ['id' => 5, 'person_id' => 3, 'date' => '2024-01-12', 'year' => 2024])->createOne();
        PersonPayment::factory(state: ['id' => 6, 'person_id' => 4, 'date' => '2024-01-13', 'year' => 2024])->createOne();

        ProtocolLine::factory(state: ['id' => 1, 'distance_id' => 1])->createOne();
        ProtocolLine::factory(state: ['id' => 2, 'distance_id' => 2, 'person_id' => 1])->createOne();
        ProtocolLine::factory(state: ['id' => 3, 'distance_id' => 1, 'person_id' => 2])->createOne();
        ProtocolLine::factory(state: ['id' => 4, 'distance_id' => 1, 'person_id' => 3])->createOne();
        ProtocolLine::factory(state: ['id' => 5, 'distance_id' => 3, 'person_id' => 4])->createOne();
    }
}
