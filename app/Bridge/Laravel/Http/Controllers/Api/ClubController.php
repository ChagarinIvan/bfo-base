<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api;

use App\Application\Dto\Club\ClubSearchDto;
use App\Application\Service\Club\ListClubs;
use App\Application\Service\Club\ListClubsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class ClubController extends Controller
{
    public function index(ListClubsService $service): JsonResponse
    {
        return new JsonResponse($service->execute(new ListClubs(new ClubSearchDto())));
    }
}
