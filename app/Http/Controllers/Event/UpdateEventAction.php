<?php

namespace App\Http\Controllers\Event;

use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UpdateEventAction extends AbstractEventAction
{
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

        if ($protocol === null && $url === null) {
            $event->save();
            return $this->redirector->action(ShowEventAction::class, [$event, $event->distances->first()]);
        } elseif ($url !== null) {
            $needConvert = false;
            $extension = 'html';
            $protocol = $this->parserService->uploadProtocol($url);
        } else {
            $needConvert = true;
            $extension = $protocol->getExtension();
            $protocol = $protocol->getContent();
        }

        $year = $event->date->format('Y');
        $protocolPath = "{$year}/{$event->date->format('Y-m-d')}_".Str::snake($event->name).'.html';

        $lineList = $this->parserService->parseProtocol($protocol, $needConvert, $extension);
        $this->storage->delete($event->file);
        $event->file = $protocolPath;

        // если не было ошибок при парсинге нового протокола,
        // то можно удалить старые строки и разряды, перед сохранением новых
        $this->eventService->deleteEvent($event);
        $this->eventService->storeEvent($event);
        //почистим кеш кубков
        foreach ($event->cups as $cup) {
            $this->cupsService->clearCupCache($cup->id);
        }

        $lineList = $this->protocolLineService->fillProtocolLines($event->id, $lineList);

        // заполняем event_id и сохраняем
        $this->storage->put($protocolPath, $protocol);
        $this->identService->identPersons($lineList);

        $this->removeLastBackUrl();
        return $this->redirector->action(ShowEventAction::class, [$event, $event->distances->first()]);
    }
}
