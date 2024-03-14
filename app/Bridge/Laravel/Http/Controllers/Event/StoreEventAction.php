<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreEventAction extends AbstractEventAction
{
    public function __invoke(string $competitionId, Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required',
            'flags' => 'array',
            'description' => 'nullable',
            'date' => 'required|date',
        ]);

        $protocol = $request->file('protocol');
        $url = $request->get('obelarus_net');
        $event = new Event($formParams);

        if ($protocol === null && $url === null) {
            return $this->redirector->action(ShowCreateEventFormAction::class);
        }

        if ($url !== null) {
            $needConvert = false;
            $extension = 'html';
            $protocol = $this->parserService->uploadProtocol($url);
        } else {
            $needConvert = true;
            $extension = $protocol->getMimeType();
            $protocol = $protocol->getContent();
        }

        $event->competition_id = (int) $competitionId;
        $year = $event->date->format('Y');

        $protocolPath = "{$year}/{$event->date->format('Y-m-d')}_" . Str::snake($event->name) . '.html';
        $event->file = $protocolPath;

        $lineList = $this->parserService->parseProtocol($protocol, $needConvert, $extension);
        $event->save();
        $lineList = $this->protocolLineService->fillProtocolLines($event->id, $lineList);
        $this->identService->identPersons($lineList);

        $this->storage->put($protocolPath, $protocol);
        $this->removeLastBackUrl();

        return $this->redirector->action(ShowEventAction::class, [$event, $event->distances->first()]);
    }
}
