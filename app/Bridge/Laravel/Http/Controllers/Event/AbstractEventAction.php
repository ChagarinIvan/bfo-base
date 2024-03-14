<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Bridge\Laravel\Http\Controllers\AbstractAction;
use App\Services\ClubsService;
use App\Services\CupsService;
use App\Services\EventService;
use App\Services\ParserService;
use App\Services\ProtocolLineIdentService;
use App\Services\ProtocolLineService;
use App\Services\ViewActionsService;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Routing\Redirector;

class AbstractEventAction extends AbstractAction
{
    public function __construct(
        protected ViewActionsService $viewActionsService,
        protected Redirector $redirector,
        protected EventService $eventService,
        protected ParserService $parserService,
        protected CupsService $cupsService,
        protected ClubsService $clubsService,
        protected ProtocolLineIdentService $identService,
        protected ProtocolLineService $protocolLineService,
        protected Filesystem $storage,
    ) {
        parent::__construct($viewActionsService, $redirector);
    }

    protected function isCompetitionsRoute(): bool
    {
        return true;
    }
}
