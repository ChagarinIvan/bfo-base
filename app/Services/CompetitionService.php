<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Competition;
use App\Models\Year;
use Illuminate\Support\Collection;
use RuntimeException;

class CompetitionService
{
    public function __construct(private EventService $eventService)
    {
    }

    public function deleteCompetition(Competition $competition): void
    {
        foreach ($competition->events as $event) {
            $this->eventService->deleteEvent($event);
        }
        $competition->delete();
    }

    public function getCompetition(int $competitionId): Competition
    {
        $competition = Competition::find($competitionId);
        if ($competition) {
            return $competition;
        }
        throw new RuntimeException('Wrong competition id.');
    }

    public function getYearCompetitions(Year $year): Collection
    {
        return Competition::where('from', '>=', "{$year->value}-01-01")
            ->where('to', '<=', "{$year->value}-12-31")
            ->orderByDesc('from')
            ->get();
    }

    public function fillAndStore(Competition $competition, array $formParams): Competition
    {
        $competition->name = $formParams['name'];
        $competition->description = $formParams['description'];
        $competition->from = $formParams['from'];
        $competition->to = $formParams['to'];
        $competition->save();

        return $competition;
    }
}
