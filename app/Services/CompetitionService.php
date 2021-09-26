<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Competition;
use App\Repositories\CompetitionsRepository;

class CompetitionService
{
    private EventService $eventService;
    private CompetitionsRepository $competitionsRepository;

    public function __construct(EventService $eventService, CompetitionsRepository $competitionsRepository) {
        $this->eventService = $eventService;
        $this->competitionsRepository = $competitionsRepository;
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
        $competition = $this->competitionsRepository->findCompetition($competitionId);
        if ($competition) {
            return $competition;
        }
        throw new \RuntimeException('Wrong competition id.');
    }
}
