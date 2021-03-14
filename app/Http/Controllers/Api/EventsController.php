<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class EventsController extends BaseController
{
    public function index(int $competitionId): JsonResponse
    {
        $events = Event::whereCompetitionId($competitionId)
            ->get()
            ->makeHidden(['created_at', 'updated_at']);

        return response()->json($events);
    }
}
