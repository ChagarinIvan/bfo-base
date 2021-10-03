<?php

declare(strict_types=1);

namespace App\Http\Controllers\Flags;

use App\Models\Flag;
use Illuminate\Contracts\View\View;

class ShowEditFlagFormAction extends AbstractFlagsAction
{
    public function __invoke(Flag $flag): View
    {
        return $this->view('flags.edit', ['flag' => $flag,]);
    }
}
