<?php

declare(strict_types=1);

namespace App\Http\Controllers\Competition;

use App\Http\Controllers\AbstractRedirectAction;
use App\Services\BackUrlService;
use App\Services\CompetitionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

class DeleteCompetitionAction extends AbstractRedirectAction
{
    private CompetitionService $competitionService;

    public function __construct(Redirector $redirector, BackUrlService $backUrlService, CompetitionService $competitionService)
    {
        parent::__construct($redirector, $backUrlService);
        $this->competitionService = $competitionService;
    }

    public function __invoke(int $year, int $competitionId): RedirectResponse
    {
        $competition = $this->competitionService->getCompetition($competitionId);
        $this->competitionService->deleteCompetition($competition);

        return $this->redirector->action(ShowCompetitionsListAction::class, ['year' => $year]);
    }
}
