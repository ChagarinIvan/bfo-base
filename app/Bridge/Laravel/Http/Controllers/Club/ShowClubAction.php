<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Club;

use App\Application\Dto\Person\PersonSearchDto;
use App\Application\Service\Club\Exception\ClubNotFound;
use App\Application\Service\Club\ViewClub;
use App\Application\Service\Club\ViewClubService;
use App\Application\Service\Person\ListPersons;
use App\Application\Service\Person\ListPersonsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use function compact;

class ShowClubAction extends BaseController
{
    use ClubAction;

    public function __invoke(
        string $id,
        ViewClubService $service,
        ListPersonsService $personsService,
    ): View|RedirectResponse {
        try {
            $club = $service->execute(new ViewClub($id));
        } catch (ClubNotFound) {
            return $this->redirectTo404Error();
        }

        $persons = $personsService->execute(new ListPersons(new PersonSearchDto(clubId: $club->id)));

        /** @see /resources/views/clubs/show.blade.php */
        return $this->view('clubs.show', compact('club', 'persons'));
    }
}
