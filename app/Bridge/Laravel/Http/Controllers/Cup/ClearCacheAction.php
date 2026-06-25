<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cup;

use App\Application\Service\Cup\ClearCupCache;
use App\Application\Service\Cup\ClearCupCacheService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ClearCacheAction extends BaseController
{
    use CupAction;

    public function __invoke(string $cupId, ClearCupCacheService $service): RedirectResponse
    {
        $service->execute(new ClearCupCache($cupId));

        return $this->redirector->action(ShowCupAction::class, [$cupId]);
    }
}
