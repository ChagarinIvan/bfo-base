<?php

namespace App\Http\Controllers\Flags;

use App\Models\Flag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowEditFlagFormAction extends AbstractFlagsAction
{
    public function __invoke(Flag $flag): View|RedirectResponse
    {
        return $this->view('flags.edit', ['flag' => $flag,]);
    }
}
