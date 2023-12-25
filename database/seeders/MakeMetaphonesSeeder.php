<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Services\PersonPromptService;
use Illuminate\Database\Seeder;

/**
 * php artisan db:seed --class=MakeMetaphonesSeeder
 */
class MakeMetaphonesSeeder extends Seeder
{
    public function __construct(private readonly PersonPromptService $service)
    {
    }

    public function run(): void
    {
        foreach ($this->service->all() as $prompt) {
            $this->service->storePersonPrompt($prompt);
        }
    }
}
