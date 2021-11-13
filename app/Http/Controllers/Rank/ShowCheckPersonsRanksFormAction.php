<?php

namespace App\Http\Controllers\Rank;

use Illuminate\Contracts\View\View;

class ShowCheckPersonsRanksFormAction extends AbstractRankAction
{
    public function __invoke(): View
    {
        return $this->view('ranks.check');
    }
}
