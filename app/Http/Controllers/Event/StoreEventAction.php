<?php

declare(strict_types=1);

namespace App\Http\Controllers\Event;

use App\Exceptions\ParsingException;
use App\Http\Controllers\AbstractRedirectAction;
use App\Http\Controllers\Error\Show404ErrorAction;
use App\Models\Event;
use App\Services\IdentService;
use App\Services\ParserService;
use App\Services\ProtocolLineService;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class StoreEventAction extends AbstractRedirectAction
{
    private ParserService $parserService;
    private IdentService $identService;
    private ExceptionHandler $exceptionHandler;
    private ProtocolLineService $protocolLineService;
    private Filesystem $storage;

    public function __construct(
        Redirector $redirector,
        ParserService $parserService,
        IdentService $identService,
        ExceptionHandler $exceptionHandler,
        ProtocolLineService $protocolLineService,
        Filesystem $storage,
    ) {
        parent::__construct($redirector);
        $this->parserService = $parserService;
        $this->identService = $identService;
        $this->exceptionHandler = $exceptionHandler;
        $this->protocolLineService = $protocolLineService;
        $this->storage = $storage;
    }

    public function __invoke(int $competitionId, Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required',
            'flags' => 'array',
            'description' => 'nullable',
            'date' => 'required|date',
        ]);

        $protocol = $request->file('protocol');
        if ($protocol === null) {
            return $this->redirector->action(ShowEditEventFormAction::class);
        }

        $event = new Event($formParams);
        $event->competition_id = $competitionId;
        $year = $event->date->format('Y');

        $protocolPath = $year .'/'.$protocol->getClientOriginalName();
        $event->file = $protocolPath;

        try {
            $lineList = $this->parserService->parserProtocol($protocol);
            $event->save();
            $lineList = $this->protocolLineService->fillProtocolLines($event->id, $lineList);
            $this->identService->identPersons($lineList);
        } catch (\Exception $e) {
            $e = new ParsingException($e->getMessage());
            $e->setEvent($event);
            $this->exceptionHandler->report($e);
            return $this->redirector->action(Show404ErrorAction::class);
        }

        $this->storage->putFileAs($year, $protocol, $protocol->getClientOriginalName());
        return $this->redirector->action(ShowEventAction::class, [$event]);
    }
}
