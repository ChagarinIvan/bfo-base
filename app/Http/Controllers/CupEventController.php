<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Cup;
use App\Models\CupEvent;
use App\Models\Event;
use App\Models\Group;
use App\Models\ProtocolLine;
use App\Services\CalculatingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Contracts\View\View;

class CupEventController extends BaseController
{
    public const DEFAULT_POINTS = 1000;

    public function delete(int $cupId, int $cupEventId): RedirectResponse
    {
        CupEvent::find($cupEventId)->delete();
        return redirect("/cups/{$cupId}/show");
    }

    public function create(int $cupId): View
    {
        $cup = Cup::with('events')->find($cupId);
        $events = Event::with('competition')
            ->where('date', 'LIKE', "%{$cup->year}%")
            ->whereNotIn('id', $cup->events->pluck('event_id'))
            ->get();

        return view('cup.events.create', [
            'cup' => $cup,
            'events' => $events,
        ]);
    }

    public function show(int $cupId, int $cupEventId, int $groupId): View
    {
        $cup = Cup::with('groups')->find($cupId);
        if ($groupId === 0) {
            /** @var Group $group */
            $group = $cup->groups->first();
            $groupId = $group->id;
        }
        $cupEvent = CupEvent::with(['event.competition'])->find($cupEventId);
        $protocolLines = ProtocolLine::whereEventId($cupEvent->event_id)
            ->whereGroupId($groupId)
            ->get();

        $cupEventPoints = CalculatingService::calculateEvent($cupEvent, $protocolLines);

        return view('cup.events.show', [
            'cup' => $cup,
            'cupEvent' => $cupEvent,
            'protocolLines' => $protocolLines,
            'cupEventPoints' => $cupEventPoints,
            'groupId' => $groupId,
        ]);
    }

    public function edit(int $cupId, int $cupEventId): View
    {
        $cup = Cup::find($cupId);
        $cupEvent = CupEvent::find($cupEventId);
        $events = Event::where('date', 'LIKE', "%{$cup->year}%")
            ->get();

        return view('cup.events.edit', [
            'cup' => $cup,
            'cupEvent' => $cupEvent,
            'events' => $events,
        ]);
    }

    public function update(int $cupId, int $cupEventId, Request $request): RedirectResponse
    {
        $formData = $request->validate([
            'event' => 'required|numeric',
            'points' => 'required|numeric',
        ]);

        $cupEvent = CupEvent::find($cupEventId);
        $cupEvent->event_id = $formData['event'];
        $cupEvent->cup_id = $cupId;
        $cupEvent->points = $formData['points'];
        $cupEvent->save();

        return redirect("/cups/{$cupId}/show");
    }

    public function store(int $cupId, Request $request): RedirectResponse
    {
        $formData = $request->validate([
            'event' => 'required|numeric',
            'points' => 'required|numeric',
        ]);

        $cupEvent = new CupEvent();
        $cupEvent->event_id = $formData['event'];
        $cupEvent->cup_id = $cupId;
        $cupEvent->points = $formData['points'];
        $cupEvent->save();

        return redirect("/cups/{$cupId}/show");
    }
}
