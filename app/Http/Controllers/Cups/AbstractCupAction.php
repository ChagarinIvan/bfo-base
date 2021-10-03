<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Http\Controllers\AbstractAction;
use App\Repositories\CupsRepository;
use App\Repositories\EventsRepository;
use App\Services\BackUrlService;
use App\Services\CupEventsService;
use App\Services\CupsService;
use App\Services\GroupsService;
use App\Services\ViewActionsService;
use Illuminate\Routing\Redirector;

abstract class AbstractCupAction extends AbstractAction
{
    protected GroupsService $groupsService;
    protected CupsRepository $cupsRepository;
    protected CupEventsService $cupEventsService;
    protected EventsRepository $eventsRepository;
    protected CupsService $cupsService;

    public function __construct(
        ViewActionsService $viewService,
        GroupsService $groupsService,
        CupsRepository $cupsRepository,
        CupEventsService $cupEventsService,
        CupsService $cupsService,
        EventsRepository $eventsRepository,
        Redirector $redirector,
        BackUrlService $backUrlService,
    ) {
        parent::__construct($viewService, $redirector, $backUrlService);
        $this->groupsService = $groupsService;
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
