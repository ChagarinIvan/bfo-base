<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Cup;
use App\Models\CupEvent;
use App\Models\Event;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Contracts\View\View;

class CupEventController extends BaseController
{
    public const DEFAULT_POINTS = 1000;

    public function delete(int $cupId, int $eventId): RedirectResponse
    {
        CupEvent::whereCupId($cupId)
            ->whereEventId($eventId)
            ->delete();

        return redirect("/cups/{$cupId}/show");
    }

    public function create(int $cupId): View
    {
        $cup = Cup::with('events')->find($cupId);
        $events = Event::with('competition')
            ->where('date', 'LIKE', "%{$cup->year}%")
            ->whereNotIn('id', $cup->events->pluck('event_id'))
            ->orderBy('date')
            ->get();

        return view('cup.events.create', [
            'cup' => $cup,
            'events' => $events,
        ]);
    }

    public function show(int $cupId, int $eventId, int $groupId)
    {
        $cup = Cup::with('groups')->find($cupId);
        if ($groupId === 0) {
            /** @var Group $group */
            $group = $cup->groups->first();
            return redirect("/cups/{$cupId}/events/{$eventId}/show/{$group->id}");
        }

        $group = Group::find($groupId);
        /** @var CupEvent $cupEvent */
        $cupEvent = CupEvent::with(['event.competition', 'cup'])
            ->whereCupId($cupId)
            ->whereEventId($eventId)
            ->get()->first();

        $cupType = $cup->cupType();
        $cupEventPoints = $cupType->calculateEvent($cupEvent, $group);

        return view('cup.events.show', [
            'cup' => $cup,
            'cupEvent' => $cupEvent,
            'cupEventPoints' => $cupEventPoints,
            'groupId' => $groupId,
        ]);
    }

    public function edit(int $cupId, int $eventId): View
    {
        $cup = Cup::find($cupId);
        /** @var CupEvent $cupEvent */
        $cupEvent = CupEvent::whereCupId($cupId)
            ->whereEventId($eventId)
            ->get()->first();

        $events = Event::where('date', 'LIKE', "%{$cup->year}%")
            ->get();

        return view('cup.events.edit', [
            'cup' => $cup,
            'cupEvent' => $cupEvent,
            'events' => $events,
        ]);
    }

    public function update(int $cupId, int $eventId, Request $request): RedirectResponse
    {
        $formData = $request->validate([
            'event' => 'required|numeric',
            'points' => 'required|numeric',
        ]);

        /** @var CupEvent $cupEvent */
        $cupEvent = CupEvent::whereCupId($cupId)
            ->whereEventId($eventId)
            ->get()->first();

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
