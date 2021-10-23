<?php

declare(strict_types=1);

namespace App\Http\Controllers\Flags;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowCreateFlagFormAction extends AbstractFlagsAction
{
    public function __invoke(): View|RedirectResponse
    {
        return $this->view('flags.create');
    }
}
