<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Services\ClubsService;
use Illuminate\Database\Seeder;

class ClubNormalNameSeeder extends Seeder
{
    public function __construct(private ClubsService $service)
    {
    }

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $clubs = $this->service->getAllClubs();
        foreach ($clubs as $club) {
            $club->normalize_name = $this->service::normalizeName($club->name);
            $club->save();
        }
    }
}
