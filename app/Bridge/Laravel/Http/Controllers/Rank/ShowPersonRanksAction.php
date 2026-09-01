<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Rank;

use App\Application\Service\Person\Exception\PersonNotFound;
use App\Application\Service\Person\ListPersonRankHistory;
use App\Application\Service\Person\ViewPerson;
use App\Application\Service\Person\ViewPersonService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ShowPersonRanksAction extends BaseController
{
    use RankAction;

    public function __invoke(
        string $personId,
        ViewPersonService $personService,
        ListPersonRankHistory $history,
    ): RedirectResponse|View {
        try {
            $person = $personService->execute(new ViewPerson($personId));
        } catch (PersonNotFound) {
            return $this->redirectTo404Error();
        }

        $ranks = $history->execute((int) $personId);

        /** @see /resources/views/ranks/show-person-ranks.blade.php */
        return $this->view('ranks.show-person-ranks', [
            'ranks' => $ranks,
            'actualRank' => $person->currentRankId,
            'actualRankFinishedOn' => $person->currentRankFinishedOn,
            'person' => $person,
        ]);
    }
}
