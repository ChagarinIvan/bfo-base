<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Club;

use App\Application\Dto\Person\LegacySearchPersonDto;
use App\Application\Service\Club\Exception\ClubNotFound;
use App\Application\Service\Club\ViewClub;
use App\Application\Service\Club\ViewClubService;
use App\Application\Service\Person\ListLegacyPersons;
use App\Application\Service\Person\ListLegacyPersonsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ShowClubAction extends BaseController
{
    use ClubAction;

    /**
     * @url /clubs/{clubId}/show
     */
    public function __invoke(
        string $clubId,
        ViewClubService $service,
        ListLegacyPersonsService $personsService,
    ): RedirectResponse|View {
        try {
            $club = $service->execute(new ViewClub($clubId));
        } catch (ClubNotFound) {
            return $this->redirectTo404Error();
        }

        $persons = $personsService->execute(new ListLegacyPersons(new LegacySearchPersonDto(clubId: $club->id)));

        /** @see /resources/views/clubs/show.blade.php */
        return $this->view('clubs.show', ['club' => $club, 'persons' => $persons]);
    }
}
