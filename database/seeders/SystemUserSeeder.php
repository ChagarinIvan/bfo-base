<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Person;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Seeder;
use function explode;
use function mb_convert_encoding;
use function preg_match;
use function str_getcsv;

class SystemUserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $user = new User();
        $user->id = 1;
        $user->email = 'bfo.base@orient.by';
        $user->password = '';
        $user->save();
    }
}
