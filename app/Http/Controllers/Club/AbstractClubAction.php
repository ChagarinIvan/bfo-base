<?php

namespace App\Http\Controllers\Club;

use App\Http\Controllers\AbstractAction;
use App\Services\ClubsService;
use App\Services\PersonsService;
use App\Services\ViewActionsService;
use Illuminate\Routing\Redirector;

abstract class AbstractClubAction extends AbstractAction
{
    protected PersonsService $personsService;
    protected ClubsService $clubsService;

    public function __construct(
        ViewActionsService $viewService,
        Redirector $redirector,
        PersonsService $personsService,
        ClubsService $clubsService,
    ) {
        parent::__construct($viewService, $redirector);
        $this->personsService = $personsService;
        $this->clubsService = $clubsService;
    }

    protected function isClubsRoute(): bool
    {
        return true;
    }
}
