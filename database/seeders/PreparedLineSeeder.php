<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProtocolLine;
use App\Services\IdentService;
use Illuminate\Database\Seeder;

class PreparedLineSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        foreach (ProtocolLine::all() as $line) {
            $line->prepared_line = IdentService::prepareLine($line->makeIdentLine());
            $line->save();
        }
    }
}
