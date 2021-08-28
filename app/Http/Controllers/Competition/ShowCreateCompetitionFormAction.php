<?php

declare(strict_types=1);

namespace App\Http\Controllers\Competition;

use Illuminate\Contracts\View\View;

class ShowCreateCompetitionFormAction extends AbstractCompetitionViewAction
{
    public function __invoke(int $year): View
    {
        return $this->view('competitions.create', ['year' => $year,]);
    }
}
