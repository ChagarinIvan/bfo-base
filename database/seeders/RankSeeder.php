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
        Competition::factory(state: ['id' => 1001])->createOne();
        Event::factory(state: ['id' => 1001, 'competition_id' => 1001, 'date' => '2024-05-08'])->createOne();
        Distance::factory(state: ['id' => 1001, 'event_id' => 1001])->createOne();
        Person::factory(state: ['id' => 1001])->createOne();
        ProtocolLine::factory(state: ['distance_id' => 1001, 'person_id' => 1001, 'activate_rank' => '2024-05-08', 'complete_rank' => Rank::FIRST_RANK])->createOne();

        Rank::factory(state: ['id' => 1001, 'person_id' => 1001, 'event_id' => 1001, 'rank' => Rank::FIRST_RANK, 'start_date' => '2024-05-08', 'finish_date' => '2024-06-21', 'activated_date' => '2020-08-29'])->createOne();
    }
}
