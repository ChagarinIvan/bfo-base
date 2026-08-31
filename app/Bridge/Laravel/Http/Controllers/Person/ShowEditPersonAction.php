<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use App\Application\Dto\Club\LegacySearchClubDto;
use App\Application\Service\Club\ListLegacyClubs;
use App\Application\Service\Club\ListLegacyClubsService;
use App\Application\Service\Person\Exception\PersonNotFound;
use App\Application\Service\Person\ViewPerson;
use App\Application\Service\Person\ViewPersonService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ShowEditPersonAction extends BaseController
{
    use PersonAction;

    public function __invoke(
        string $id,
        ViewPersonService $viewPersonService,
        ListLegacyClubsService $listClubsService,
    ): RedirectResponse|View {
        try {
            $person = $viewPersonService->execute(new ViewPerson($id));
        } catch (PersonNotFound) {
            return $this->redirectTo404Error();
        }

        $clubs = $listClubsService->execute(new ListLegacyClubs(new LegacySearchClubDto()));

        /** @see /resources/views/persons/edit.blade.php */
        return $this->view('persons.edit', ['person' => $person, 'clubs' => $clubs]);
    }
}
