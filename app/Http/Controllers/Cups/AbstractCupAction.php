<?php
declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Http\Controllers\AbstractAction;
use App\Repositories\EventsRepository;
use App\Services\CupEventsService;
use App\Services\CupsService;
use App\Services\ViewActionsService;
use Illuminate\Routing\Redirector;

abstract class AbstractCupAction extends AbstractAction
{
    public function __construct(
        protected ViewActionsService $viewService,
        protected CupEventsService $cupEventsService,
        protected CupsService $cupsService,
        protected EventsRepository $eventsRepository,
        protected Redirector $redirector,
    ) {
        parent::__construct($viewService, $redirector);
    }

    protected function isCupsRoute(): bool
    {
        return true;
    }
}
