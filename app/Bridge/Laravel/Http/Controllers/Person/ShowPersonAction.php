<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use App\Application\Service\Person\Exception\PersonNotFound;
use App\Application\Service\Person\ViewPerson;
use App\Application\Service\Person\ViewPersonService;
use App\Application\Service\Rank\ActivePersonRank;
use App\Application\Service\Rank\ActivePersonRankService;
use App\Services\PersonsService;
use App\Services\RankService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ShowPersonAction extends BaseController
{
    use PersonAction;

    public function __invoke(
        string $id,
        PersonsService $personsService,
        ViewPersonService $service,
        RankService $rankService,
        ActivePersonRankService $personRankService,
    ): View|RedirectResponse {
        try {
            $person = $service->execute(new ViewPerson($id, true));
        } catch (PersonNotFound) {
            return $this->redirector->action(ShowPersonsListAction::class);
        }

        dump($person);
        /** @see /resources/views/persons/show.blade.php */
        return $this->view('persons.show', [
            'person' => $person,
            'rank' => $personRankService->execute(new ActivePersonRank($person->id))
        ]);
    }
}
