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
use App\Domain\ProtocolLine\ProtocolLine;
use App\Models\Year;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;

class ElkPathCupLineSeeder extends Seeder
{
    use WithFaker;

    public function run(): void
    {
        Group::factory(state: ['id' => 101, 'name' => 'NightTrailElite-Ж'])->createOne();
        Competition::factory(state: ['id' => 101, 'name' => 'Паўночны вецер', 'from' => '2026-01-31', 'to' => '2026-01-31'])->createOne();
        Event::factory(state: ['id' => 101, 'competition_id' => 101, 'name' => 'дзень 1', 'date' => '2026-01-31'])->createOne();
        Distance::factory(state: ['id' => 101, 'group_id' => 101, 'event_id' => 101, 'length' => 2600, 'points' => 26])->createOne();
        Person::factory(state: ['id' => 101, 'lastname' => 'Журомская', 'firstname' => 'Вероника', 'citizenship' => 'belarus', 'birthday' => '1998-01-01'])->createOne();
        ProtocolLine::factory(state: ['id' => 101, 'lastname' => 'Журомская', 'firstname' => 'Вероника', 'club' => 'КСО «Верас»', 'year' => '1998', 'rank' => 'МС', 'time' => '00:18:48', 'place' => '1', 'complete_rank' => 'КМС', 'distance_id' => 101, 'person_id' => 101])->createOne();
        Cup::factory(state: ['id' => 101, 'name' => 'Elk path 2026', 'year' => Year::y2026, 'type' => CupType::ELK_PATH])->createOne();
        CupEvent::factory(state: ['id' => 101, 'cup_id' => 101, 'event_id' => 101, 'points' => 1100])->createOne();
    }
}
