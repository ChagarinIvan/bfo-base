<?php

namespace App\Http\Controllers\Person;

use App\Http\Controllers\AbstractAction;
use App\Services\PersonsService;
use App\Services\RankService;
use App\Services\ViewActionsService;
use Illuminate\Routing\Redirector;

abstract class AbstractPersonAction extends AbstractAction
{
    protected RankService $rankService;
    protected PersonsService $personsService;

    public function __construct(
        ViewActionsService $viewService,
        Redirector $redirector,
        RankService $rankService,
        PersonsService $personsService,
    ) {
        parent::__construct($viewService, $redirector);
        $this->rankService = $rankService;
        $this->personsService = $personsService;
    }

    protected function isPersonsRoute(): bool
    {
        return true;
    }
}
