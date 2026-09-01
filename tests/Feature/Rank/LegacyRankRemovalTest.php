<?php

declare(strict_types=1);

namespace Tests\Feature\Rank;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LegacyRankRemovalTest extends TestCase
{
    #[Test]
    public function removed_rank_entry_points_are_not_registered(): void
    {
        $actions = collect(Route::getRoutes()->getRoutes())
            ->map(static fn ($route): string => (string) $route->getActionName())
            ->all();

        $this->assertNotContains('App\\Bridge\\Laravel\\Http\\Controllers\\Rank\\ShowRanksListAction', $actions);
        $this->assertNotContains('App\\Bridge\\Laravel\\Http\\Controllers\\Rank\\CheckPersonsRanksAction', $actions);
        $this->assertNotContains('App\\Bridge\\Laravel\\Http\\Controllers\\Rank\\ExportPersonsRanksAction', $actions);
        $this->assertNotContains('App\\Bridge\\Laravel\\Http\\Controllers\\Rank\\RefillPersonRanksAction', $actions);
    }

    #[Test]
    public function removed_rank_implementation_files_do_not_exist(): void
    {
        foreach ([
            'app/Services/RankService.php',
            'app/Repositories/RanksRepository.php',
            'app/Infrastructure/Laravel/Eloquent/Rank/LegacyRank.php',
            'app/Application/Service/Rank/ActivePersonRankService.php',
            'app/Bridge/Laravel/Console/Commands/RecalculatingRanks.php',
        ] as $path) {
            $this->assertFileDoesNotExist(base_path($path));
        }
    }
}
