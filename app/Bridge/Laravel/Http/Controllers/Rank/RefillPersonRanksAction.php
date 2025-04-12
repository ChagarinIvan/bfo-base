<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Rank;

use App\Application\Service\Person\Exception\PersonNotFound;
use App\Application\Service\Person\ViewPerson;
use App\Application\Service\Person\ViewPersonService;
use App\Application\Service\Rank\RefillPersonRanksService;
use App\Application\Service\Rank\RefillPersonRanks;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class RefillPersonRanksAction extends BaseController
{
    use RankAction;

    public function __invoke(
        string $personId,
        ViewPersonService $personService,
        RefillPersonRanksService $ranksService,
    ): View|RedirectResponse {
        try {
            $personService->execute(new ViewPerson($personId));
        } catch (PersonNotFound) {
            return $this->redirectTo404Error();
        }

        $ranksService->execute(new RefillPersonRanks($personId));
        dd('finished');

        return $this->redirector->action(ShowPersonRanksAction::class, [$personId]);
    }
}
