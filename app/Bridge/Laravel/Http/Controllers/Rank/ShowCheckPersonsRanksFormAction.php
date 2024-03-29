<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Rank;

use Illuminate\Contracts\View\View;

class ShowCheckPersonsRanksFormAction extends AbstractRankAction
{
    public function __invoke(): View
    {
        return $this->view('ranks.check');
    }
}
