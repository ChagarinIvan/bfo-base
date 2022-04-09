<?php

namespace App\Http\Controllers\Person;

use App\Http\Controllers\AbstractAction;
use App\Services\ClubsService;
use App\Services\PersonsService;
use App\Services\RankService;
use App\Services\ViewActionsService;
use Illuminate\Routing\Redirector;

abstract class AbstractPersonAction extends AbstractAction
{
    public function __construct(
        protected readonly ViewActionsService $viewService,
        protected readonly Redirector $redirector,
        protected readonly RankService $rankService,
        protected readonly PersonsService $personsService,
        protected readonly ClubsService $clubsService,
    ) {
        parent::__construct($viewService, $redirector);
    }

    protected function isPersonsRoute(): bool
    {
        return true;
    }
}
