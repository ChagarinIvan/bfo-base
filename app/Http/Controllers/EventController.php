<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\ParsingException;
use App\Facades\System;
use App\Models\Event;
use App\Models\Flag;
use App\Models\Group;
use App\Models\Parser\ParserFactory;
use App\Models\ProtocolLine;
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
        return view('events.create', [
            'competitionId' => $competitionId,
            'flags' => Flag::all(),
        ]);
    }

    public function edit(int $eventId): View
    {
        return view('events.edit', [
            'event' => Event::find($eventId),
            'flags' => Flag::all(),
        ]);
    }

    public function addFlags(int $eventId): View
    {
        return view('events.add-flags', [
            'event' => Event::find($eventId),
            'flags' => Flag::all(),
        ]);
    }

    public function setFlags(int $eventId, int $flagId): RedirectResponse
    {
        $event = Event::find($eventId);
        $event->flags()->attach($flagId);

        return redirect("/competitions/events/{$eventId}/add-flags");
    }

    public function deleteFlags(int $eventId, int $flagId): RedirectResponse
    {
        $event = Event::find($eventId);
        $event->flags()->detach($flagId);

        return redirect("/competitions/events/{$eventId}/add-flags");
    }

    public function delete(int $eventId): RedirectResponse
    {
        $event = Event::find($eventId);
        $competitionId = $event->competition_id;
        ProtocolLine::whereEventId($eventId)->delete();
        $event->delete();
        return redirect("/competitions/{$competitionId}/show");
    }

    public function update(int $eventId, Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'date' => 'required|date',
        ]);

        $protocol = $request->file('protocol');
        $event = Event::find($eventId);

        if ($event === null) {
            throw new RuntimeException('Нету ивента с таким id - '.$eventId);
        }

        $event->fill($formParams);
        $event->save();

        if ($protocol === null) {
            return redirect("/competitions/events/{$event->id}/show");
        }

        $parser = ParserFactory::createParser($protocol);
        try {
            $lineList = $parser->parse($protocol);
        } catch (ParsingException $e) {
            $e->setEvent($event);
            report($e);
            return redirect('/404');
        }

        $lineList->transform(function (array $lineData) {
            $protocolLine = new ProtocolLine($lineData);
            $group = Group::where('name', str_replace(' ', '', $lineData['group']))->first();
            if ($group === null) {
                throw new RuntimeException('Wrong group '.$lineData['group']);
            }
            $protocolLine->group_id = $group->id;
            return $protocolLine;
        });

        $event->protocolLines()->delete();

        $lineList->each(function (ProtocolLine $protocolLine) use ($event) {
            $protocolLine->event_id = $event->id;
            $protocolLine->save();
        });

        System::setNeedRecheck();

        return redirect("/competitions/events/{$event->id}/show");
    }

    public function store(int $competitionId, Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required',
            'flags' => 'array',
            'description' => 'nullable',
            'date' => 'required|date',
        ]);

        $protocol = $request->file('protocol');
        if ($protocol === null) {
            throw new RuntimeException('empty file');
        }

        $parser = ParserFactory::createParser($protocol);
        $event = new Event($formParams);
        $event->competition_id = $competitionId;

        try {
            $lineList = $parser->parse($protocol);
        } catch (ParsingException $e) {
            $e->setEvent($event);
            report($e);
            return redirect('/404');
        }
        $lineList->transform(function (array $lineData) {
            $protocolLine = new ProtocolLine($lineData);
            $group = Group::whereName(str_replace(' ', '', $lineData['group']))->first();
            if ($group === null) {
                throw new RuntimeException('Wrong group '.$lineData['group']);
            }
            $protocolLine->group_id = $group->id;
            return $protocolLine;
        });


        $event->save();

        $lineList->each(function (ProtocolLine $protocolLine) use ($event) {
            $protocolLine->event_id = $event->id;
            $protocolLine->save();
        });

        System::setNeedRecheck();

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
        $numbers = $event->protocolLines->pluck('runner_number');
        $isRelay = count($numbers) > count($numbers->unique());
        $protocolLines = $event->protocolLines->groupBy('group_id')
            ->sortKeys();
        $groups = Group::find($protocolLines->keys());
        $groupAnchors = $groups->pluck('name');

        if ($isRelay) {
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
