<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use App\Application\Service\Person\Exception\PersonNotFound;
use App\Application\Service\Person\ViewPerson;
use App\Application\Service\Person\ViewPersonService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ShowPersonAction extends BaseController
{
    use PersonAction;

    public function __invoke(
        string $id,
        ViewPersonService $service,
    ): RedirectResponse|View {
        try {
            $person = $service->execute(new ViewPerson($id, includeProtocolLines: true));
        } catch (PersonNotFound) {
            return $this->redirector->action(ShowPersonsListAction::class);
        }

        /** @see /resources/views/persons/show.blade.php */
        return $this->view('persons.show', [
            'person' => $person,
            'rank' => $person->currentRankId,
            'rankFinishedOn' => $person->currentRankFinishedOn,
        ]);
    }
}
