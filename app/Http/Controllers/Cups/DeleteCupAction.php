<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Models\Cup;
use Illuminate\Http\RedirectResponse;

class DeleteCupAction extends AbstractCupAction
{
    public function __invoke(Cup $cup): RedirectResponse
    {
        $year = $cup->year;
        $this->cupsService->deleteCup($cup);
        return $this->redirector->action(ShowCupsListAction::class, [$year]);
    }
}
