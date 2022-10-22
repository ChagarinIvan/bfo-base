<?php

namespace App\Bridge\Laravel\Controller\Club;

use App\Application\Service\Club\ViewClub;
use App\Application\Service\Club\ViewClubService;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class ShowClubAction extends Controller
{
    public function __construct(protected ViewFactory $viewService)
    {}

    public function __invoke(string $id, ViewClubService $service): View
    {
        return $this->viewService->make('clubs.show', [
            'club' => $service->execute(new ViewClub($id)),
//            'persons' => $this->personsService->getClubPersons($club->id),
        ]);
    }
}
