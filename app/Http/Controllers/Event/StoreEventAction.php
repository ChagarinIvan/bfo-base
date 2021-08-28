<?php

declare(strict_types=1);

namespace App\Http\Controllers\Event;

use App\Exceptions\ParsingException;
use App\Http\Controllers\AbstractRedirectAction;
use App\Http\Controllers\Error\Show404ErrorAction;
use App\Models\Competition;
use App\Models\Event;
use App\Services\IdentService;
use App\Services\ParserService;
use App\Services\ProtocolLineService;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Storage;

class StoreEventAction extends AbstractRedirectAction
{
    private ParserService $parserService;
    private IdentService $identService;
    private ExceptionHandler $exceptionHandler;
    private ProtocolLineService $protocolLineService;

    public function __construct(
        Redirector $redirector,
        ParserService $parserService,
        IdentService $identService,
        ExceptionHandler $exceptionHandler,
        ProtocolLineService $protocolLineService,
    ) {
        parent::__construct($redirector);
        $this->parserService = $parserService;
        $this->identService = $identService;
        $this->exceptionHandler = $exceptionHandler;
        $this->protocolLineService = $protocolLineService;
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

        Storage::putFileAs($year, $protocol, $protocol->getClientOriginalName());
        return $this->redirector->action(ShowEventAction::class, [$event]);
    }
}
