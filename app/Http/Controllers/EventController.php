<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\ParsingException;
use App\Models\Competition;
use App\Models\Distance;
use App\Models\Event;
use App\Models\Flag;
use App\Models\Group;
use App\Models\Parser\ParserFactory;
use App\Models\ProtocolLine;
use App\Services\IdentService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
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

    public function sum(int $competitionId): View
    {
        $competition = Competition::with('events')->find($competitionId);
        return view('events.sum', [
            'competition' => $competition,
        ]);
    }

    /**
     * Объединяем несколько протоколов в один.
     * у всех строк протокола должен быть персон ИД
     *
     * @param int $competitionId
     * @param Request $request
     * @return RedirectResponse
     */
    public function unit(int $competitionId, Request $request): RedirectResponse
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

        return redirect("/competitions/events/{$newEvent->id}/show");
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
        $event->distances()->delete();
        $event->protocolLines()->delete();
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

        if ($protocol === null) {
            $event->save();
            return redirect("/competitions/events/{$event->id}/show");
        }
        $year = $event->date->format('Y');
        $protocolPath = $year .'/'.$protocol->getClientOriginalName();

        try {
            $lineList = $this->parserProtocol($protocol);
            Storage::delete($event->file);
            $event->file = $protocolPath;
            $event->save();
            $event->distances()->delete();
            $event->protocolLines()->delete();
            $lineList = $this->fillProtocolLines($event, $lineList);

            // если не было ошибок при парсинге новго протокола,
            // то можно удалить старые строки, перед сохранением новых
            // заполняем event_id и сохраняем
            Storage::putFileAs($year, $protocol, $protocol->getClientOriginalName());

            $this->identPersons($lineList);
        } catch (Exception $e) {
            return $this->errorHandling($e, $event);
        }

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

        $event = new Event($formParams);
        $event->competition_id = $competitionId;
        $year = $event->date->format('Y');

        $protocolPath = $year .'/'.$protocol->getClientOriginalName();
        $event->file = $protocolPath;

        try {
            $lineList = $this->parserProtocol($protocol);
            $event->save();
            $lineList = $this->fillProtocolLines($event, $lineList);
            $this->identPersons($lineList);
        } catch (Exception $e) {
            return $this->errorHandling($e, $event);
        }

        Storage::putFileAs($year, $protocol, $protocol->getClientOriginalName());
        return redirect("/competitions/events/{$event->id}/show");
    }

    public function show(int $eventId): View
    {
        $event = Event::with(['protocolLines.person.club'])->find($eventId);
        $withPoints = false;
        foreach ($event->protocolLines as $protocolLine) {
            $withPoints = $protocolLine->points !== null;
            if ($withPoints) {
                break;
            }
        }
        $numbers = $event->protocolLines->pluck('runner_number');
        $isRelay = count($numbers) > count($numbers->unique());
        $protocolLines = $event->protocolLines->groupBy('distance_id')
            ->sortKeys();
        $distances = Distance::with(['group'])->find($protocolLines->keys());
        $groupAnchors = $distances->pluck('group');

        if ($isRelay) {
            $protocolLines->transform(function (Collection $lines) {
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
                'distances' => $distances,
                'withPoints' => $withPoints,
                'groupAnchors' => $groupAnchors,
            ]);
        }

        return view('events.show_others', [
            'event' => $event,
            'lines' => $protocolLines,
            'distances' => $distances,
            'withPoints' => $withPoints,
            'groupAnchors' => $groupAnchors,
        ]);
    }

    private function errorHandling(Exception $e, Event $event): RedirectResponse
    {
        if (!$e instanceof ParsingException) {
            $e = new ParsingException($e->getMessage());
        }
        $e->setEvent($event);
        report($e);
        return redirect('/404');
    }

    /**
     * По протоколу определяет необходимый парсер
     * Парсер разбирает протокол на сырые массивы данных из строк
     * Из сырых строк наполняются модели ProtocolLine
     *
     * @param UploadedFile $protocol
     * @return Collection
     * @throw Exception
     */
    private function parserProtocol(UploadedFile $protocol): Collection
    {
        $parser = ParserFactory::createParser($protocol);
        return $parser->parse($protocol);
    }

    private function fillProtocolLines(Event $event, Collection $lineList): Collection
    {
        $groups = Group::all();

        // из массива сырых данных формирует модель записи протокола
        // определяем группу и формируем идентификационную строку
        return $lineList->transform(function (array $lineData) use ($groups, $event) {
            $protocolLine = new ProtocolLine($lineData);
            $protocolLine->prepared_line = $protocolLine->makeIdentLine();
            $groupName = str_replace(' ', '', $lineData['group']);
            /** @var Group $group */
            $group = $groups->firstWhere('name', $groupName);
            if ($group === null) {
                throw new RuntimeException('Wrong group '.$lineData['group']);
            }

            $length = $lineData['distance']['length'] ?? 0;
            $points = $lineData['distance']['points'] ?? 0;

            $distances = Distance::whereGroupId($group->id)
                ->whereEventId($event->id)
                ->whereLength($length)
                ->wherePoints($points)
                ->get();

            if ($distances->count() === 0) {
                $distance = new Distance();
                $distance->group_id = $group->id;
                $distance->event_id = $event->id;
                $distance->length = $length;
                $distance->points = $points;
                $distance->save();
            } else {
                $distance = $distances->first();
            }

            $protocolLine->distance_id = $distance->id;
            $protocolLine->save();
            return $protocolLine;
        });
    }

    /**
     * Запускаем процесс идентификации людей в строчках протокола
     * состоит из 2 частей:
     * - по прямому совпадению идентификатора (на лету)
     * - по расстоянию левенштейна (в очередь)
     *
     * @param Collection $protocolLines
     */
    private function identPersons(Collection $protocolLines): void
    {
        // пробуем идентифицировать людей из нового протокола прямым подобием идентификационных строк
        $identService = new IdentService();
        $protocolLines = $identService->simpleIdent($protocolLines);
        $protocolLines = $protocolLines->pluck('prepared_line')->unique();
        $identService->pushIdentLines($protocolLines);
    }
}
