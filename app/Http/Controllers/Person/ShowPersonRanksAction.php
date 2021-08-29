<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Models\Person;
use Illuminate\Contracts\View\View;

class ShowPersonRanksAction extends AbstractPersonViewAction
{
    public function __invoke(Person $person): View
    {
        $ranks = $person->ranks->sortByDesc('finish_date');

        return $this->view('ranks.show-person-ranks', [
            'ranks' => $ranks,
            'actualRank' => $ranks->last(),
            'person' => $person,
        ]);
    }
}
