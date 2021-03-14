<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\ProtocolLine;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class ResultsController extends BaseController
{
    public function index(int $eventId): JsonResponse
    {
        $lines = ProtocolLine::whereEventId($eventId)->get();
        return response()->json($lines);
    }
}
