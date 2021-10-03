<?php

declare(strict_types=1);

namespace App\Http\Controllers\Event;

use App\Exceptions\ParsingException;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreEventAction extends AbstractEventAction
{
    public function __invoke(int $competitionId, Request $request): RedirectResponse
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

        try {
            if ($protocol === null && $url === null) {
                return $this->redirector->action(ShowCreateEventFormAction::class);
            } elseif ($url !== null) {
                $needConvert = false;
                $protocol = $this->parserService->uploadProtocol($url);
            } else {
                $needConvert = true;
                $protocol = $protocol->getContent();
            }

            $event->competition_id = $competitionId;
            $year = $event->date->format('Y');

            $protocolPath = "{$year}/{$event->date->format('Y-m-d')}_".Str::snake($event->name).'.html';
            $event->file = $protocolPath;

            $lineList = $this->parserService->parserProtocol($protocol, $needConvert);
            $event->save();
            $lineList = $this->protocolLineService->fillProtocolLines($event->id, $lineList);
            $this->identService->identPersons($lineList);
        } catch (\Exception $e) {
            $e = new ParsingException($e->getMessage());
            $e->setEvent($event);
            $this->exceptionHandler->report($e);
            return $this->redirectToError();
        }

        $this->storage->put($protocolPath, $protocol);
        $this->removeLastBackUrl();
        return $this->redirector->action(ShowEventAction::class, [$event]);
    }
}
