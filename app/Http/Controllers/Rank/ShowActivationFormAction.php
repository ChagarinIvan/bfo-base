<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rank;

use App\Models\Person;
use App\Models\Rank;
use Illuminate\Contracts\View\View;

class ShowActivationFormAction extends AbstractRankAction
{
    public function __invoke(Person $person, Rank $rank): View
    {
        return $this->view('ranks.show-person-rank-activation', compact('person', 'rank'));
    }
}
