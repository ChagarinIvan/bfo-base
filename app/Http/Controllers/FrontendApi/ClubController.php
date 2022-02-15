<?php

declare(strict_types=1);

namespace App\Http\Controllers\FrontendApi;

use App\Http\Resources\ClubResource;
use App\Services\ClubsService;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Routing\Controller;

class ClubController extends Controller
{
    public function __construct(private readonly ClubsService $clubsService)
    {}

    public function index(): ResourceCollection
    {
        return ClubResource::collection($this->clubsService->getAllClubs());
    }
}
