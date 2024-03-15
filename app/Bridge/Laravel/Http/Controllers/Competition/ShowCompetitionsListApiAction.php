<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Competition;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;

final class ShowCompetitionsListApiAction extends BaseController
{
    use CompetitionAction;

    public function __invoke(): View {
        /** @see /resources/views/competitions/index-api.blade.php */
        return $this->view('competitions.index-api');
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
