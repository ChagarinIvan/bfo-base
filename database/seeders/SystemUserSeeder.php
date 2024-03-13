<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use function random_bytes;

class SystemUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = new User();
        $user->id = 10;
        $user->email = 'bfo.base@orient.by';
        $user->password = bin2hex(random_bytes(5));
        $user->save();
    }
}
