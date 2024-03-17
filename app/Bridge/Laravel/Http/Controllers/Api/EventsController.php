<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api;

use App\Domain\Event\Event;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class EventsController extends BaseController
{
    public function index(string $competitionId): JsonResponse
    {
        /** @var Collection $events */
        $events = Event::whereCompetitionId($competitionId)->get();
        $events->makeHidden(['created_at', 'updated_at']);

        return response()->json($events);
    }
}
