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
}
