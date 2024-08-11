<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Person\Person;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;

class RankSeeder extends Seeder
{
    use WithFaker;

    public function run(): void
    {
        Competition::factory(state: ['id' => 1001, 'from' => '2019-04-14', 'to' => '2019-04-14'])->createOne();
        Event::factory(state: ['id' => 1001, 'competition_id' => 1001, 'date' => '2019-04-14'])->createOne();
        Distance::factory(state: ['id' => 1001, 'event_id' => 1001])->createOne();

        Competition::factory(state: ['id' => 1002, 'from' => '2019-05-14', 'to' => '2019-05-14'])->createOne();
        Event::factory(state: ['id' => 1002, 'competition_id' => 1002, 'date' => '2019-05-14'])->createOne();
        Distance::factory(state: ['id' => 1002, 'event_id' => 1002])->createOne();

        Competition::factory(state: ['id' => 1003, 'from' => '2020-08-29', 'to' => '2020-08-29'])->createOne();
        Event::factory(state: ['id' => 1003, 'competition_id' => 1003, 'date' => '2020-08-29'])->createOne();
        Distance::factory(state: ['id' => 1003, 'event_id' => 1003])->createOne();

        Competition::factory(state: ['id' => 1004, 'from' => '2021-08-29', 'to' => '2021-07-29'])->createOne();
        Event::factory(state: ['id' => 1004, 'competition_id' => 1004, 'date' => '2021-07-29'])->createOne();
        Distance::factory(state: ['id' => 1004, 'event_id' => 1004])->createOne();

        Competition::factory(state: ['id' => 1007, 'from' => '2023-07-01', 'to' => '2023-07-01'])->createOne();
        Event::factory(state: ['id' => 1007, 'competition_id' => 1007, 'date' => '2023-07-01'])->createOne();
        Distance::factory(state: ['id' => 1007, 'event_id' => 1007])->createOne();

        Competition::factory(state: ['id' => 1005, 'from' => '2024-05-08', 'to' => '2024-05-08'])->createOne();
        Event::factory(state: ['id' => 1005, 'competition_id' => 1005, 'date' => '2024-05-08'])->createOne();
        Distance::factory(state: ['id' => 1005, 'event_id' => 1005])->createOne();

        Competition::factory(state: ['id' => 1006, 'from' => '2024-06-22', 'to' => '2024-06-22'])->createOne();
        Event::factory(state: ['id' => 1006, 'competition_id' => 1006, 'date' => '2024-06-22'])->createOne();
        Distance::factory(state: ['id' => 1006, 'event_id' => 1006])->createOne();

        Person::factory(state: ['id' => 1001])->createOne();

        ProtocolLine::factory(state: ['distance_id' => 1001, 'person_id' => 1001, 'activate_rank' => '2019-04-14', 'complete_rank' => Rank::SECOND_RANK])->createOne();
        ProtocolLine::factory(state: ['distance_id' => 1002, 'person_id' => 1001, 'activate_rank' => '2019-05-14', 'complete_rank' => Rank::SECOND_RANK])->createOne();
        ProtocolLine::factory(state: ['distance_id' => 1003, 'person_id' => 1001, 'activate_rank' => '2020-08-29', 'complete_rank' => Rank::FIRST_RANK])->createOne();
        ProtocolLine::factory(state: ['distance_id' => 1004, 'person_id' => 1001, 'activate_rank' => '2021-08-29', 'complete_rank' => Rank::FIRST_RANK])->createOne();
        ProtocolLine::factory(state: ['distance_id' => 1007, 'person_id' => 1001, 'activate_rank' => '2023-07-01', 'complete_rank' => Rank::FIRST_RANK])->createOne();
        ProtocolLine::factory(state: ['distance_id' => 1005, 'person_id' => 1001, 'activate_rank' => '2024-05-08', 'complete_rank' => Rank::FIRST_RANK])->createOne();
        ProtocolLine::factory(state: ['distance_id' => 1006, 'person_id' => 1001, 'activate_rank' => null, 'complete_rank' => Rank::SMC_RANK])->createOne();
    }
}
