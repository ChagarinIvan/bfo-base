<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Competition;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;

final class ShowCreateCompetitionFormAction extends BaseController
{
    use CompetitionAction;

    public function __invoke(): View
    {
        /** @see /resources/views/competitions/create.blade.php */
        return $this->view('competitions.create');
    }
}
