<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Event;
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
