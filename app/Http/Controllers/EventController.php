<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Group;
use App\Models\Parser\ParserFactory;
use App\Models\ProtocolLine;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use RuntimeException;

class EventController extends Controller
{
    public function create(int $competitionId)
    {
        return view('events.create', ['competitionId' => $competitionId]);
    }

    public function edit(int $eventId)
    {
        $event = Event::find($eventId);
        return view('events.edit', ['event' => $event]);
    }

    public function delete(int $eventId)
    {
        $event = Event::find($eventId);
        $competitionId = $event->competition_id;
        $event->delete();
        return redirect("/competitions/{$competitionId}/show");
    }


    public function update(int $eventId, Request $request)
    {
        $formParams = $request->validate([
            'name' => 'required',
            'type' => 'nullable',
            'description' => 'nullable',
            'date' => 'required|date',
        ]);

        $protocol = $request->file('protocol');
        if ($protocol === null) {
            throw new \RuntimeException('empty file');
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

        return redirect("/competitions/events/{$event->id}/show");
    }

    public function store(int $competitionId, Request $request)
    {
        $formParams = $request->validate([
            'name' => 'required',
            'type' => 'nullable',
            'description' => 'nullable',
            'date' => 'required|date',
        ]);

        $protocol = $request->file('protocol');
        if ($protocol === null) {
            throw new \RuntimeException('empty file');
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

        return redirect("/competitions/events/{$event->id}/show");
    }

    public function show(int $eventId): View
    {
        $event = Event::find($eventId);
        $protocolLines = $event->protocolLines;
        $withPoints = false;
        foreach ($protocolLines as $protocolLine) {
            $withPoints = $protocolLine->points !== null;
            if ($withPoints) {
                break;
            }
        }
        $protocolLines = $protocolLines->groupBy('group_id')
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
                    $length = strlen($protocolLine->serial_number);
                    $newNumberIndex = substr($protocolLine->serial_number, 1, $length - 1);
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
