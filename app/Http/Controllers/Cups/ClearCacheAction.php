<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Models\Cup;
use Illuminate\Http\RedirectResponse;

class ClearCacheAction extends AbstractCupAction
{
    public function __invoke(Cup $cup): RedirectResponse
    {
        $this->cupsService->clearCupCache($cup->id);
        return $this->redirector->action(ShowCupAction::class, [$cup]);
    }
}
