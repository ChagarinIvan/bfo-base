<?php

namespace App\Http\Controllers\Cups;

use App\Models\Cup;
use Illuminate\Http\RedirectResponse;

class DeleteCupAction extends AbstractCupAction
{
    public function __invoke(Cup $cup): RedirectResponse
    {
        $this->cupsService->deleteCup($cup);

        return $this->redirector->action(ShowCupsListAction::class, [$cup->year]);
    }
}
