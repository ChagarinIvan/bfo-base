<?php

declare(strict_types=1);

namespace App\Http\Controllers\Event;

use App\Models\Distance;
use App\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class ShowEventAction extends AbstractEventAction
{
    public function __invoke(Event $event): View
    {
        $withPoints = false;
        foreach ($event->protocolLines as $protocolLine) {
            $withPoints = $protocolLine->points !== null;
            if ($withPoints) {
                break;
            }
        }
        $numbers = $event->protocolLines->pluck('runner_number')->diff([0]);
        $isRelay = count($numbers) > count($numbers->unique());
        $protocolLines = $event->protocolLines->groupBy('distance_id')
            ->sortKeys();

        $distances = Distance::with(['group'])->find($protocolLines->keys());

        $groupAnchors = $distances->pluck('group');

        if ($isRelay) {
            return $this->showRelayEvent($event, $protocolLines, $distances, $withPoints, $groupAnchors);
        }

        return $this->view('events.show_others', [
            'event' => $event,
            'lines' => $protocolLines,
            'distances' => $distances,
            'withPoints' => $withPoints,
            'groupAnchors' => $groupAnchors,
        ]);
    }

    private function showRelayEvent(
        Event $event,
        Collection $protocolLines,
        Collection $distances,
        bool $withPoints,
        Collection $groupAnchors,
    ): View {
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

        return $this->view('events.show_relay', [
            'event' => $event,
            'groupedLines' => $protocolLines,
            'distances' => $distances,
            'withPoints' => $withPoints,
            'groupAnchors' => $groupAnchors,
        ]);
    }
}
