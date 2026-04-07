<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Club;

use App\Application\Service\Club\ListClubsService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;
use function compact;

class ShowClubsListAction extends BaseController
{
    use ClubAction;

    /**
     * @url /clubs
     */
    public function __invoke(ListClubsService $service): View
    {
        $clubs = $service->execute();

        /** @see /resources/views/clubs/index.blade.php */
        return $this->view('clubs.index', ['clubs' => $clubs]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
