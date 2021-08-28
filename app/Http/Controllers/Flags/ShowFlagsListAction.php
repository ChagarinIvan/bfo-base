<?php

declare(strict_types=1);

namespace App\Http\Controllers\Flags;

use App\Models\Flag;
use Illuminate\Contracts\View\View;

class ShowFlagsListAction extends AbstractFlagsViewAction
{
    public function __invoke(): View
    {
        return $this->view('flags.index', ['flags' => Flag::all()]);
    }
}
