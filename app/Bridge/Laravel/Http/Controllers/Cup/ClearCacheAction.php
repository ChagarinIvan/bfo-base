<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cup;

use App\Domain\Cup\Cup;
use App\Services\CupsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ClearCacheAction extends BaseController
{
    use CupAction;

    public function __invoke(string $cupId, CupsService $cupsService): RedirectResponse
    {
        $cupsService->clearCupCache((int) $cupId);

        return $this->redirector->action(ShowCupAction::class, [$cupId]);
    }
}
