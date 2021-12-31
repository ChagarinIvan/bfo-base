<?php

namespace App\Services;

use App\Models\Competition;
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

    /**
     * @param int $year
     * @return Collection|Competition[]
     */
    public function getYearCompetitions(int $year): Collection
    {
        return Competition::where('from', '>=', "{$year}-01-01")
            ->where('to', '<=', "{$year}-12-31")
            ->orderByDesc('from')
            ->get();
    }
}
