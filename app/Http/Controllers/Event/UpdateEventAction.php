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

class UpdateEventAction extends AbstractRedirectAction
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

    public function __invoke(Event $event, Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'date' => 'required|date',
        ]);

        $protocol = $request->file('protocol');
        $event->fill($formParams);

        if ($protocol === null) {
            $event->save();
            return $this->redirector->action(ShowEventAction::class, [$event]);
        }
        $year = $event->date->format('Y');
        $protocolPath = $year .'/'.$protocol->getClientOriginalName();

        try {
            $lineList = $this->parserService->parserProtocol($protocol);
            $this->storage->delete($event->file);
            $event->file = $protocolPath;
            $event->save();
            $event->distances()->delete();
            $event->protocolLines()->delete();
            $lineList = $this->protocolLineService->fillProtocolLines($event->id, $lineList);

            // если не было ошибок при парсинге новго протокола,
            // то можно удалить старые строки, перед сохранением новых
            // заполняем event_id и сохраняем
            $this->storage->putFileAs($year, $protocol, $protocol->getClientOriginalName());
            $this->identService->identPersons($lineList);
        } catch (\Exception $e) {
            $e = new ParsingException($e->getMessage());
            $e->setEvent($event);
            $this->exceptionHandler->report($e);
            return $this->redirector->action(Show404ErrorAction::class);
        }

        return $this->redirector->action(ShowEventAction::class, [$event]);
    }
}