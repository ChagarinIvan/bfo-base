<?php

declare(strict_types=1);

namespace Tests\Feature\Rank;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LegacyRankPageRemovalTest extends TestCase
{
    #[Test]
    public function old_rank_web_routes_are_not_registered(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes())
            ->map(static fn ($route): string => $route->uri())
            ->all();

        $this->assertNotContains('ranks/person/{personId}', $routes);
        $this->assertNotContains('ranks/{protocolLineId}/activate', $routes);
        $this->assertNotContains('ranks/{protocolLineId}/update-activation', $routes);
    }

    #[Test]
    public function old_rank_templates_and_web_actions_are_removed(): void
    {
        foreach ([
            'resources/views/ranks/show-person-ranks.blade.php',
            'resources/views/ranks/show-person-rank-activation.blade.php',
            'resources/views/ranks/show-edit-rank-activation-date.blade.php',
            'app/Bridge/Laravel/Http/Controllers/Rank/ShowPersonRanksAction.php',
            'app/Bridge/Laravel/Http/Controllers/Rank/ShowActivationFormAction.php',
            'app/Bridge/Laravel/Http/Controllers/Rank/ShowEditActivationDateFormAction.php',
        ] as $path) {
            $this->assertFileDoesNotExist(base_path($path));
        }
    }
}
