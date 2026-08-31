<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Competition;

use App\Application\Dto\Competition\SearchCompetitionDto;
use App\Application\Service\Competition\ListCompetitions;
use App\Application\Service\Competition\ListCompetitionsService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;

final class ShowCompetitionsListAction extends BaseController
{
    use CompetitionAction;

    /**
     * @url /competitions
     */
    public function __invoke(
        SearchCompetitionDto    $search,
        ListCompetitionsService $service,
    ): View {
        $competitions = $service->execute(new ListCompetitions($search))->setPerPage(100)->items();

        /** @see /resources/views/competitions/index.blade.php */
        return $this->view('competitions.index', [
            'competitions' => $competitions,
            'selectedYear' => $search->year,
        ]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
