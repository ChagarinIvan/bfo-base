<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Cup;
use App\Models\Group;
use App\Models\ProtocolLine;
use App\Services\CalculatingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class CupController extends BaseController
{
    public function index(): View
    {
        $allCups = Cup::all();
        $years = $allCups->pluck('year');
        $groupedCups = $allCups->groupBy(function (Cup $cup) {
            return $cup->year;
        });
        $groupedCups = $groupedCups->sortKeysDesc();

        return view('cup.index', [
            'groupedCups' => $groupedCups,
            'years' => $years,
        ]);
    }

    public function create(): View
    {
        $groups = Group::all();
        return view('cup.create', [
            'groups' => $groups,
        ]);
    }

    public function update(int $cupId, Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required|unique:competitions|max:255',
            'year' => 'required|digits:4',
            'groups' => 'required|array',
            'events_count' => 'required|numeric',
            'groups.*' => 'integer',
        ]);

        $cup = Cup::find($cupId);
        $cup->name = $formParams['name'];
        $cup->year = $formParams['year'];
        $cup->events_count = $formParams['events_count'];
        $cup->save();
        $cup->groups()->sync($formParams['groups']);

        return redirect("/cups/{$cup->id}/show");
    }

    public function show(int $cupId): View
    {
        $cup = Cup::with('events')
            ->find($cupId);
        $events = $cup->events()
            ->join('events', 'events.id', '=', 'cup_events.event_id')
            ->orderBy('events.date')
            ->get();
        return view('cup.show', ['cup' => $cup, 'events' => $events]);
    }

    public function edit(int $cupId): View
    {
        $cup = Cup::find($cupId);
        $groups = Group::all();

        return view('cup.edit', [
            'cup' => $cup,
            'groups' => $groups,
        ]);
    }

    public function table(int $cupId, int $groupId): View
    {
        $cup = Cup::with(['groups', 'events.event'])->find($cupId);
        if ($groupId === 0) {
            /** @var Group $group */
            $group = $cup->groups->first();
            $groupId = $group->id;
        } else {
            $group = Group::find($groupId);
        }
        $cupIds = $cup->events->pluck('event_id');
        $protocolLines = ProtocolLine::with('person')
            ->whereIn('event_id', $cupIds)
            ->whereNotNull('person_id')
            ->whereGroupId($groupId)
            ->get();

        $events = $cup->events()
            ->join('events', 'events.id', '=', 'cup_events.event_id')
            ->orderBy('events.date')
            ->get();

        if ($protocolLines->isEmpty()) {
            $cupPoints = [];
        } else {
            $cupPoints = CalculatingService::calculateCup($cup, $events, $protocolLines);
        }
        $protocolLines = $protocolLines->groupBy('person_id');

        return view('cup.table', [
            'cup' => $cup,
            'events' => $events,
            'cupPoints' => $cupPoints,
            'protocolLines' => $protocolLines,
            'activeGroup' => $group,
        ]);
    }

    public function delete(int $cupId): RedirectResponse
    {
        Cup::find($cupId)->delete();
        return redirect('cups');
    }

    public function store(Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required|unique:competitions|max:255',
            'year' => 'required|digits:4',
            'events_count' => 'required|numeric',
            'groups' => 'required|array',
            'groups.*' => 'integer',
        ]);

        $cup = new Cup();
        $cup->name = $formParams['name'];
        $cup->year = $formParams['year'];
        $cup->events_count = $formParams['events_count'];
        $cup->save();
        $cup->groups()->sync($formParams['groups']);

        return redirect("/cups/{$cup->id}/show");
    }
}
