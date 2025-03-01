<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Rank;

use Illuminate\Contracts\View\View;

class ShowCheckPersonsRanksFormAction extends AbstractRankAction
{
    public function __invoke(): View
    {
        /** @see /resources/views/ranks/check.blade.php */
        return $this->view('ranks.check');
    }
}
