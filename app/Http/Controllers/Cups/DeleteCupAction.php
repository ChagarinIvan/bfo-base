<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Http\Controllers\AbstractRedirectAction;
use App\Models\Cup;
use Illuminate\Http\RedirectResponse;

class DeleteCupAction extends AbstractRedirectAction
{
    public function __invoke(Cup $cup): RedirectResponse
    {
        $year = $cup->year;
        $cup->delete();
        return $this->redirector->action(ShowCupsListAction::class, [$year]);
    }
}
