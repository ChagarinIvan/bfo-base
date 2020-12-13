<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;

class GroupsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        foreach (Group::GROUPS as $groupName) {
            $group = new Group();
            $group->name = $groupName;
            $group->save();
        }
    }
}
