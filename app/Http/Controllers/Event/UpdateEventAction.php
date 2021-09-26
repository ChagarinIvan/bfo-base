<?php

declare(strict_types=1);

namespace App\Http\Controllers\Event;

use App\Exceptions\ParsingException;
use App\Http\Controllers\AbstractRedirectAction;
use App\Http\Controllers\Error\Show404ErrorAction;
use App\Models\Event;
use App\Services\BackUrlService;
use App\Services\EventService;
use App\Services\IdentService;
use App\Services\ParserService;
use App\Services\ProtocolLineService;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Str;

class UpdateEventAction extends AbstractRedirectAction
{
    private EventService $eventService;
    private ParserService $parserService;
    private IdentService $identService;
    private ExceptionHandler $exceptionHandler;
    private ProtocolLineService $protocolLineService;
    private Filesystem $storage;

    public function __construct(
        Redirector $redirector,
        BackUrlService $backUrlService,
        ParserService $parserService,
        IdentService $identService,
        ExceptionHandler $exceptionHandler,
        ProtocolLineService $protocolLineService,
        Filesystem $storage,
        EventService $eventService,
    ) {
        parent::__construct($redirector, $backUrlService);
        $this->parserService = $parserService;
        $this->identService = $identService;
        $this->exceptionHandler = $exceptionHandler;
        $this->protocolLineService = $protocolLineService;
        $this->storage = $storage;
        $this->eventService = $eventService;
    }

    public function __invoke(Event $event, Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'date' => 'required|date',
        ]);

        $protocol = $request->file('protocol');
        $url = $request->get('obelarus_net');
        $event->fill($formParams);

        try {
            if ($protocol === null && $url === null) {
                $event->save();
                return $this->redirector->action(ShowEventAction::class, [$event]);
            } elseif ($url !== null) {
                $needConvert = false;
                $protocol = $this->parserService->uploadProtocol($url);
            } else {
                $needConvert = true;
                $protocol = $protocol->getContent();
            }

            $year = $event->date->format('Y');
            $protocolPath = "{$year}/{$event->date->format('Y-m-d')}_".Str::snake($event->name).'.html';

            $lineList = $this->parserService->parserProtocol($protocol, $needConvert);
            $this->storage->delete($event->file);
            $event->file = $protocolPath;

            // если не было ошибок при парсинге нового протокола,
            // то можно удалить старые строки и разряды, перед сохранением новых
            $this->eventService->deleteEvent($event);
            $this->eventService->storeEvent($event);
            $lineList = $this->protocolLineService->fillProtocolLines($event->id, $lineList);

            // заполняем event_id и сохраняем
            $this->storage->put($protocolPath, $protocol);
            $this->identService->identPersons($lineList);
        } catch (\Exception $e) {
            $e = new ParsingException($e->getMessage());
            $e->setEvent($event);
            $this->exceptionHandler->report($e);
            return $this->redirector->action(Show404ErrorAction::class);
        }

        $this->removeLastBackUrl();
        return $this->redirector->action(ShowEventAction::class, [$event]);
    }
}
