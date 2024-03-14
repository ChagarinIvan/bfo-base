<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Competition;

use App\Application\Dto\Competition\CompetitionSearchDto;
use App\Application\Service\Competition\ListCompetitions;
use App\Application\Service\Competition\ListCompetitionsService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;

final class ShowCompetitionsListAction extends BaseController
{
    use CompetitionAction;

    public function __invoke(
        string $year,
        CompetitionSearchDto $search,
        ListCompetitionsService $service,
    ): View {
        $competitions = $service->execute(new ListCompetitions($search));

        /** @see /resources/views/competitions/index.blade.php */
        return $this->view('competitions.index', [
            'competitions' => $competitions,
            'selectedYear' => $year,
        ]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
