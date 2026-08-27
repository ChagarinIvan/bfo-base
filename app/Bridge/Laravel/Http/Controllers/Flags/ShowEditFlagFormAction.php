<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Flags;

use App\Models\Flag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowEditFlagFormAction extends AbstractFlagsAction
{
    public function __invoke(Flag $flag): RedirectResponse|View
    {
        return $this->view('flags.edit', ['flag' => $flag,]);
    }
}
