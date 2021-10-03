<?php

declare(strict_types=1);

namespace App\Http\Controllers\Flags;

use Illuminate\Contracts\View\View;

class ShowCreateFlagFormAction extends AbstractFlagsAction
{
    public function __invoke(): View
    {
        return $this->view('flags.create');
    }
}
