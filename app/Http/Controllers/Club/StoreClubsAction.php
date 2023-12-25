<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Repositories\ClubsRepository;
use App\Services\Club\ClubFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreClubsAction extends AbstractClubAction
{
    public function __invoke(Request $request, ClubFactory $factory, ClubsRepository $clubs): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required',
        ]);

        $club = $factory->create($formParams['name']);
        $clubs->add($club);

        return $this->redirector->action(ShowClubsListAction::class);
    }
}
