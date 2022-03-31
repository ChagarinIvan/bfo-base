<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\Year;
use Illuminate\Support\Collection;

class CompetitionService
{
    public function __construct(private EventService $eventService)
    {}

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
        throw new \RuntimeException('Wrong competition id.');
    }

    public function getYearCompetitions(Year $year): Collection
    {
        return Competition::where('from', '>=', "{$year->value}-01-01")
            ->where('to', '<=', "{$year->value}-12-31")
            ->orderByDesc('from')
            ->get();
    }
}
