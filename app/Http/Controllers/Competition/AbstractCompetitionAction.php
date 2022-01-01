<?php

namespace App\Http\Controllers\Competition;

use App\Http\Controllers\AbstractAction;
use App\Services\CompetitionService;
use App\Services\ViewActionsService;
use Illuminate\Routing\Redirector;

abstract class AbstractCompetitionAction extends AbstractAction
{
    public function __construct(
        protected ViewActionsService $viewService,
        protected Redirector $redirector,
        protected CompetitionService $competitionService
    ) {
        parent::__construct($viewService, $redirector);
    }

    protected function isCompetitionsRoute(): bool
    {
        return true;
    }
}
