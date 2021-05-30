<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Cup;
use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Event;
use App\Models\Group;
use App\Models\ProtocolLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

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
        $events = Event::where('date', 'LIKE', "%{$cup->year}%")
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
            ->whereNotNull('person_id')
            ->get();

        $cupEventPoints = $this->calculatePoints($cupEvent, $protocolLines);

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

    /**
     * @param CupEvent $cupEvent
     * @param Collection $protocolLines
     * @return array<int, CupEventPoint>
     */
    private function calculatePoints(CupEvent $cupEvent, Collection $protocolLines): array
    {
        if ($protocolLines->isEmpty()) {
            return [];
        }

        $maxPoints = $cupEvent->points;
        $cupEventPointsList = [];

        $protocolLines = $protocolLines->sortByDesc(function (ProtocolLine $line) {
            return $line->time ? $line->time->diffInSeconds() : 0;
        });

        /** @var ProtocolLine $firstResult */
        $firstResult = $protocolLines->first();
        $firstResultSeconds = $firstResult->time ? $firstResult->time->secondsSinceMidnight() : 0;

        //а этапах Кубков Федерации очки начисляются по формуле:
        //O = Kus × (2W ÷ T − 1),
        //где T – результат спортсмена в секундах, W – результат победителя в секундах, Kus – коэффициент уровня соревнований.

        foreach ($protocolLines as $protocolLine) {
            /** @var ProtocolLine $protocolLine */

            if ($firstResult->id === $protocolLine->id) {
                $cupEventPoints = new CupEventPoint($cupEvent->id, $protocolLine->id, $maxPoints);
            } else {
                if ($protocolLine->time !== null) {
                    $diff = $protocolLine->time->secondsSinceMidnight();
                    $points = (int)round($maxPoints * (2 * $firstResultSeconds / $diff - 1));
                } else {
                    $points = 0;
                }

                $cupEventPoints = new CupEventPoint($cupEvent->id, $protocolLine->id, $points < 0 ? 0 : $points);
            }
            $cupEventPointsList[$cupEventPoints->protocolLineId] = $cupEventPoints;
        }

        return $cupEventPointsList;
    }
}
