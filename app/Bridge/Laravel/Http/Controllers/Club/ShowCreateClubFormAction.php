<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Club;

use App\Bridge\Laravel\Http\Controllers\Flags\AbstractFlagsAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowCreateClubFormAction extends AbstractFlagsAction
{
    public function __invoke(): View|RedirectResponse
    {
        return $this->view('clubs.create');
    }
}
