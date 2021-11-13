<?php

namespace App\Http\Controllers\Cups;

use App\Http\Controllers\AbstractAction;
use App\Repositories\EventsRepository;
use App\Services\CupEventsService;
use App\Services\CupsService;
use App\Services\GroupsService;
use App\Services\ViewActionsService;
use Illuminate\Routing\Redirector;

abstract class AbstractCupAction extends AbstractAction
{
    protected GroupsService $groupsService;
    protected CupEventsService $cupEventsService;
    protected EventsRepository $eventsRepository;
    protected CupsService $cupsService;

    public function __construct(
        ViewActionsService $viewService,
        GroupsService $groupsService,
        CupEventsService $cupEventsService,
        CupsService $cupsService,
        EventsRepository $eventsRepository,
        Redirector $redirector,
    ) {
        parent::__construct($viewService, $redirector);
        $this->groupsService = $groupsService;
        $this->cupEventsService = $cupEventsService;
        $this->eventsRepository = $eventsRepository;
        $this->cupsService = $cupsService;
        $this->redirector = $redirector;
    }

    protected function isCupsRoute(): bool
    {
        return true;
    }
}
