<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Club;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class ClubsController extends BaseController
{
    public function index(): JsonResponse
    {
        return response()->json(Club::all());
    }
}
