<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProtocolLine;
use App\Services\IdentService;
use Illuminate\Database\Seeder;

class FillIdentSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $preparedLines = ProtocolLine::distinct()
            ->whereNull('person_id')
            ->get();

        $preparedLines = $preparedLines->sortByDesc('id')
            ->pluck('prepared_line')
            ->values()
            ->unique();

        (new IdentService())->pushIdentLines($preparedLines);
    }
}
