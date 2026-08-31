<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Club;

use App\Application\Dto\Club\LegacySearchClubDto;
use App\Application\Service\Club\ListLegacyClubs;
use App\Application\Service\Club\ListLegacyClubsService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;

class ShowClubsListAction extends BaseController
{
    use ClubAction;

    /**
     * @url /clubs
     */
    public function __invoke(ListLegacyClubsService $service): View
    {
        $clubs = $service->execute(new ListLegacyClubs(new LegacySearchClubDto(withPersonsCount: true)));

        /** @see /resources/views/clubs/index.blade.php */
        return $this->view('clubs.index', ['clubs' => $clubs]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
