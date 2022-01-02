<?php

namespace App\Http\Controllers\Api;

use App\Services\ClubsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class ClubsController extends BaseController
{
    public function __construct(private ClubsService $clubsService)
    {}

    public function index(): JsonResponse
    {
        return response()->json($this->clubsService->getAllClubs());
    }
}
