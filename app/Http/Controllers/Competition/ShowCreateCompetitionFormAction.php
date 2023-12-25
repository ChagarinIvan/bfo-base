<?php

declare(strict_types=1);

namespace App\Http\Controllers\Competition;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowCreateCompetitionFormAction extends AbstractCompetitionAction
{
    public function __invoke(int $year): View|RedirectResponse
    {
        return $this->view('competitions.create', ['year' => $year,]);
    }
}
