<?php

declare(strict_types=1);

namespace App\Http\Controllers\Competition;

use App\Http\Controllers\AbstractAction;
use App\Services\BackUrlService;
use App\Services\CompetitionService;
use App\Services\ViewActionsService;
use Illuminate\Routing\Redirector;

abstract class AbstractCompetitionAction extends AbstractAction
{
    protected CompetitionService $competitionService;

    public function __construct(
        ViewActionsService $viewService,
        Redirector $redirector,
        BackUrlService $backUrlService,
        CompetitionService $competitionService
    ) {
        parent::__construct($viewService, $redirector, $backUrlService);
        $this->competitionService = $competitionService;
    }

    protected function isCompetitionsRoute(): bool
    {
        return true;
    }
}
