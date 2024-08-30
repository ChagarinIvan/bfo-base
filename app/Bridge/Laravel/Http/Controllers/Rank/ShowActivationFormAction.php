<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Rank;

use App\Domain\Person\Person;
use App\Domain\Rank\Rank;
use Illuminate\Contracts\View\View;
use function compact;

class ShowActivationFormAction extends AbstractRankAction
{
    public function __invoke(Person $person, Rank $rank): View
    {
        return $this->view('ranks.show-person-rank-activation', compact('person', 'rank'));
    }
}
