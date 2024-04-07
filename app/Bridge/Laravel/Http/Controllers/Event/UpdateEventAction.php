<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Application\Dto\Auth\UserId;
use App\Domain\Auth\Impression;
use App\Domain\Event\Event;
use App\Domain\Event\Protocol;
use App\Domain\Shared\Clock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UpdateEventAction extends AbstractEventAction
{
    use UploadHelper;

    public function __invoke(
        Event $event,
        Request $request,
        UserId $userId,
        Clock $clock,
    ): RedirectResponse {
        $formParams = $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'date' => 'required|date',
        ]);

        $file = $request->file('protocol');
        $url = $request->get('url');
        $event->fill($formParams);
        $event->updated = new Impression($clock->now(), $userId->id);

        if ($file === null && $url === null) {
            $event->save();

            return $this->redirector->action(ShowEventDistanceAction::class, [$event, $event->distances->first()]);
        }

        if ($url !== null) {
            $needConvert = false;
            $protocol = $this->uploadProtocol($url);
        } else {
            $needConvert = true;
            $protocol = new Protocol(
                $file->getContent(),
                $file->getMimeType(),
            );

        }

        $year = $event->date->format('Y');
        $protocolPath = "{$year}/{$event->date->format('Y-m-d')}_" . Str::snake($event->name) . '.html';

        $lineList = $this->parserService->parse($protocol);

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
        $this->storage->put($protocolPath, $protocol->content);
        $this->identService->identPersons($lineList);

        $this->removeLastBackUrl();
        return $this->redirector->action(ShowEventDistanceAction::class, [$event, $event->distances->first()]);
    }
}
