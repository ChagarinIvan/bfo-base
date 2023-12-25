<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class ResultsController extends BaseController
{
    public function index(int $eventId): JsonResponse
    {
        $event = Event::find($eventId);
        return response()->json($event->protocolLines);
    }
}
