<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Rank;

use App\Application\Service\Competition\Exception\CompetitionNotFound;
use App\Application\Service\Person\Exception\PersonNotFound;
use App\Application\Service\Person\ViewPerson;
use App\Application\Service\Person\ViewPersonService;
use App\Application\Service\Rank\ActivePersonRank;
use App\Application\Service\Rank\ActivePersonRankService;
use App\Application\Service\Rank\PersonRanks;
use App\Application\Service\Rank\PersonRanksService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ShowPersonRanksAction extends BaseController
{
    use RankAction;

    public function __invoke(
        string $personId,
        ViewPersonService $personService,
        PersonRanksService $ranksService,
        ActivePersonRankService $activeRankService,
    ): View|RedirectResponse {
        try {
            $person = $personService->execute(new ViewPerson($personId));
        } catch (PersonNotFound) {
            return $this->redirectTo404Error();
        }

        $activeRank = $activeRankService->execute(new ActivePersonRank($personId));
        $ranks = $ranksService->execute(new PersonRanks($personId));

        /** @see /resources/views/ranks/show-person-ranks.blade.php */
        return $this->view('ranks.show-person-ranks', [
            'ranks' => $ranks,
            'actualRank' => $activeRank,
            'person' => $person,
        ]);
    }
}
