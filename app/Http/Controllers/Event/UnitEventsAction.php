<?php

declare(strict_types=1);

namespace App\Http\Controllers\Event;

use App\Models\Distance;
use App\Models\Event;
use App\Models\ProtocolLine;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class UnitEventsAction extends AbstractEventAction
{
    public function __invoke(int $competitionId, Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'events' => 'required|array',
        ]);
        $events = Event::find($formParams['events']);
        $firstEvent = $events->first();

        $newEvent = new Event();
        $name = $events->pluck('name')->implode(' + ');
        $newEvent->name = $name;
        $newEvent->description = "Аб'яднанне этапаў: {$name}";
        $newEvent->date = $firstEvent->date;
        $newEvent->competition_id = $competitionId;
        $newEvent->save();

        $newProtocolLines = new Collection();
        $firstEventProtocolLines = $firstEvent->protocolLines->groupBy('distance.group_id');

        foreach ($events as $event) {
            if ($firstEvent->id === $event->id) {
                continue;
            }

            $eventsProtocolLines = $event->protocolLines->groupBy('distance.group_id');

            $groupsIds = $firstEventProtocolLines->keys()->merge($eventsProtocolLines->keys())->unique();

            foreach ($groupsIds as $groupId) {
                /** @var Distance $firstEventDistance */
                $firstEventDistance = Distance::whereEventId($firstEvent->id)->whereGroupId($groupId)->get()->first();
                /** @var Distance $eventDistance */
                $eventDistance = Distance::whereEventId($event->id)->whereGroupId($groupId)->get()->first();
                $distances = Distance::whereEventId($newEvent->id)->whereGroupId($groupId)->get();
                if ($distances->isNotEmpty()) {
                    $distance = $distances->first();
                } else {
                    $distance = new Distance();
                }

                if ($firstEventDistance) {
                    $distance->points += $firstEventDistance->points;
                    $distance->length += $firstEventDistance->length;
                }
                if ($eventDistance) {
                    $distance->points += $eventDistance->points;
                    $distance->length += $eventDistance->length;
                }
                $distance->group_id = $groupId;
                $distance->event_id = $newEvent->id;
                $distance->save();

                /** @var Collection $firstEventGroupProtocolLines */
                /** @var Collection $eventGroupProtocolLines */
                $firstEventGroupProtocolLines = $firstEventProtocolLines->has($groupId) ?
                    $firstEventProtocolLines->get($groupId) :
                    collect();
                $eventGroupProtocolLines = $eventsProtocolLines->has($groupId) ?
                    $eventsProtocolLines->get($groupId) :
                    collect();

                $firstEventGroupProtocolLines = $firstEventGroupProtocolLines->keyBy('person_id');
                $eventGroupProtocolLines = $eventGroupProtocolLines->keyBy('person_id');
                $personIds = $firstEventGroupProtocolLines->keys()->merge($eventGroupProtocolLines->keys())->unique();

                foreach ($personIds as $personId) {
                    /** @var ProtocolLine $firstEventProtocolLine */
                    $firstEventProtocolLine = $firstEventGroupProtocolLines->get($personId);
                    /** @var ProtocolLine $eventProtocolLine */
                    $eventProtocolLine = $eventGroupProtocolLines->get($personId);

                    if ($firstEventProtocolLine !== null) {
                        $newProtocolLine = $firstEventProtocolLine->replicate();
                        if ($eventProtocolLine !== null) {
                            if ($firstEventProtocolLine->time instanceof Carbon && $eventProtocolLine->time instanceof Carbon) {
                                $newProtocolLine->time = $newProtocolLine->time->addHours($eventProtocolLine->time->hour);
                                $newProtocolLine->time = $newProtocolLine->time->addMinutes($eventProtocolLine->time->minute);
                                $newProtocolLine->time = $newProtocolLine->time->addSeconds($eventProtocolLine->time->second);
                            } else {
                                $newProtocolLine->time = null;
                            }
                        } else {
                            $newProtocolLine->time = null;
                        }
                    } elseif ($eventProtocolLine !== null) {
                        $newProtocolLine = $eventProtocolLine->replicate();
                        $newProtocolLine->time = null;
                    } else {
                        continue;
                    }
                    $newProtocolLine->distance_id = $distance->id;
                    $newProtocolLines->push($newProtocolLine);
                }
            }

            $firstEventProtocolLines = $newProtocolLines->groupBy('distance.group_id');
            $newProtocolLines = new Collection();
            $firstEvent = $newEvent;
        }

        $number = 1;
        foreach ($firstEventProtocolLines as $groupProtocolLines) {
            /** @var Collection $groupProtocolLines */
            $groupProtocolLines = $groupProtocolLines->transform(function (ProtocolLine $line) use (&$number) {
                $line->runner_number = $number++;
                $line->time = $line->time === null ?
                    null :
                    Carbon::createFromFormat('H:i:s', $line->time->format('H:i:s'));
                return $line;
            });

            $groupProtocolLines = $groupProtocolLines->sortBy(fn (ProtocolLine $line) => $line->time ? $line->time->secondsSinceMidnight() : 86400);

            $place = 1;
            foreach ($groupProtocolLines as $line) {
                /** @var ProtocolLine $line */
                $line->place = $line->time === null ? $place : $place++;
                $line->points = null;
                $line = new ProtocolLine($line->toArray());
                $line->save();
            }
        }

        return $this->redirector->action(ShowEventAction::class, [$newEvent, $newEvent->distances->first()]);
    }
}
