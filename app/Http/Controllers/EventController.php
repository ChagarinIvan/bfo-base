<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\ParserEventJob;
use App\Models\Event;
use App\Models\Group;
use App\Models\Parser\ParserFactory;
use App\Models\ProtocolLine;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use RuntimeException;

class EventController extends Controller
{
    public function create(int $competitionId): View
    {
        return view('events.create', ['competitionId' => $competitionId]);
    }

    public function edit(int $eventId): View
    {
        $event = Event::find($eventId);
        return view('events.edit', ['event' => $event]);
    }

    public function delete(int $eventId): RedirectResponse
    {
        $event = Event::find($eventId);
        $competitionId = $event->competition_id;
        ProtocolLine::whereEventId($eventId)->delete();
        return redirect("/competitions/{$competitionId}/show");
    }

    public function update(int $eventId, Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required',
            'type' => 'nullable',
            'description' => 'nullable',
            'date' => 'required|date',
        ]);

        $protocol = $request->file('protocol');
        if ($protocol === null) {
            $event = Event::find($eventId);
            if ($event === null) {
                throw new RuntimeException('Нету ивента с таким id - '.$eventId);
            }

            $event->fill($formParams);
            $event->save();
            return redirect("/competitions/events/{$event->id}/show");
        }

        $parser = ParserFactory::createParser($protocol, $formParams['type'] ?? null);
        $lineList = $parser->parse($protocol);
        $lineList->transform(function (array $lineData) {
            $protocolLine = new ProtocolLine($lineData);
            $group = Group::where('name', str_replace(' ', '', $lineData['group']))->first();
            if ($group === null) {
                throw new RuntimeException('Wrong group '.$lineData['group']);
            }
            $protocolLine->group_id = $group->id;
            return $protocolLine;
        });

        $event = Event::find($eventId);
        if ($event === null) {
            throw new RuntimeException('Нету ивента с таким id - '.$eventId);
        }

        $event->fill($formParams);
        $event->save();
        $event->protocolLines()->delete();

        $lineList->each(function (ProtocolLine $protocolLine) use ($event) {
            $protocolLine->event_id = $event->id;
            $protocolLine->save();
        });

        ParserEventJob::dispatch($event)->delay(Carbon::now()->addMinutes(1));

        return redirect("/competitions/events/{$event->id}/show");
    }

    public function store(int $competitionId, Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required',
            'type' => 'nullable',
            'description' => 'nullable',
            'date' => 'required|date',
        ]);

        $protocol = $request->file('protocol');
        if ($protocol === null) {
            throw new RuntimeException('empty file');
        }

        $parser = ParserFactory::createParser($protocol, $formParams['type'] ?? null);
        $lineList = $parser->parse($protocol);
        $lineList->transform(function (array $lineData) {
            $protocolLine = new ProtocolLine($lineData);
            $group = Group::whereName(str_replace(' ', '', $lineData['group']))->first();
            if ($group === null) {
                throw new RuntimeException('Wrong group '.$lineData['group']);
            }
            $protocolLine->group_id = $group->id;
            return $protocolLine;
        });

        $event = new Event($formParams);
        $event->competition_id = $competitionId;
        $event->save();

        $lineList->each(function (ProtocolLine $protocolLine) use ($event) {
            $protocolLine->event_id = $event->id;
            $protocolLine->save();
        });

        ParserEventJob::dispatch($event)->delay(Carbon::now()->addMinutes());

        return redirect("/competitions/events/{$event->id}/show");
    }

    public function show(int $eventId): View
    {
        $event = Event::with('protocolLines.person.club')->find($eventId);
        $withPoints = false;
        foreach ($event->protocolLines as $protocolLine) {
            $withPoints = $protocolLine->points !== null;
            if ($withPoints) {
                break;
            }
        }
        $protocolLines = $event->protocolLines->groupBy('group_id')
            ->sortKeys();
        $groups = Group::find($protocolLines->keys());
        $groupAnchors = $groups->pluck('name');

        if (str_contains(strtolower($event->type), 'relay')) {
            $protocolLines->transform(function(Collection $lines) {
                $groupedLine = [];
                $place = 0;
                $numberIndex = 0;
                $index = 0;
                foreach ($lines as $protocolLine) {
                    $newPlace = $protocolLine->place;
                    $number = (string)$protocolLine->serial_number;
                    $length = strlen($number);
                    $newNumberIndex = substr($number, 1, $length - 1);
                    if ($newPlace !== $place || $newNumberIndex !== $numberIndex) {
                        $index++;
                    }

                    $place = $newPlace;
                    $numberIndex = $newNumberIndex;

                    $groupedLine[$index][] = $protocolLine;
                }
                return $groupedLine;
            });

            return view('events.show_relay', [
                'event' => $event,
                'groupedLines' => $protocolLines,
                'groups' => $groups,
                'withPoints' => $withPoints,
                'groupAnchors' => $groupAnchors,
            ]);
        }

        return view('events.show_others', [
            'event' => $event,
            'lines' => $protocolLines,
            'groups' => $groups,
            'withPoints' => $withPoints,
            'groupAnchors' => $groupAnchors,
        ]);
    }
}
