<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use App\Bridge\Laravel\Http\Controllers\AbstractAction;
use App\Services\ClubsService;
use App\Services\PersonsService;
use App\Services\RankService;
use App\Services\ViewActionsService;
use Illuminate\Routing\Redirector;

abstract class AbstractPersonAction extends AbstractAction
{
    public function __construct(
        protected ViewActionsService $viewService,
        protected Redirector $redirector,
        protected RankService $rankService,
        protected PersonsService $personsService,
        protected ClubsService $clubsService,
    ) {
        parent::__construct($viewService, $redirector);
    }

    protected function isPersonsRoute(): bool
    {
        return true;
    }
}
