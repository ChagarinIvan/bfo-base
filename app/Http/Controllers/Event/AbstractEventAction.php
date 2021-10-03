<?php

declare(strict_types=1);

namespace App\Http\Controllers\Event;

use App\Http\Controllers\AbstractAction;
use App\Services\BackUrlService;
use App\Services\EventService;
use App\Services\IdentService;
use App\Services\ParserService;
use App\Services\ProtocolLineService;
use App\Services\ViewActionsService;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Routing\Redirector;

class AbstractEventAction extends AbstractAction
{
    protected EventService $eventService;
    protected ParserService $parserService;
    protected IdentService $identService;
    protected ExceptionHandler $exceptionHandler;
    protected ProtocolLineService $protocolLineService;
    protected Filesystem $storage;

    public function __construct(
        ViewActionsService $viewActionsService,
        Redirector $redirector,
        BackUrlService $backUrlService,
        EventService $eventService,
        ParserService $parserService,
        IdentService $identService,
        ExceptionHandler $exceptionHandler,
        ProtocolLineService $protocolLineService,
        Filesystem $storage,
    ) {
        parent::__construct($viewActionsService, $redirector, $backUrlService);
        $this->eventService = $eventService;
        $this->parserService = $parserService;
        $this->identService = $identService;
        $this->exceptionHandler = $exceptionHandler;
        $this->protocolLineService = $protocolLineService;
        $this->storage = $storage;
    }

    protected function isCompetitionsRoute(): bool
    {
        return true;
    }
}
