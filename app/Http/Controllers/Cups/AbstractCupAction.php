<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Http\Controllers\AbstractViewAction;
use App\Repositories\CupsRepository;
use App\Repositories\EventsRepository;
use App\Repositories\GroupsRepository;
use App\Services\CupEventsService;
use App\Services\CupsService;
use App\Services\ViewActionsService;
use Illuminate\Routing\Redirector;

abstract class AbstractCupAction extends AbstractViewAction
{
    protected GroupsRepository $groupsRepository;
    protected CupsRepository $cupsRepository;
    protected CupEventsService $cupEventsService;
    protected EventsRepository $eventsRepository;
    protected CupsService $cupsService;
    protected Redirector $redirector;

    public function __construct(
        ViewActionsService $viewService,
        GroupsRepository $groupsRepository,
        CupsRepository $cupsRepository,
        CupEventsService $cupEventsService,
        CupsService $cupsService,
        EventsRepository $eventsRepository,
        Redirector $redirector
    ) {
        parent::__construct($viewService);
        $this->groupsRepository = $groupsRepository;
        $this->cupsRepository = $cupsRepository;
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
