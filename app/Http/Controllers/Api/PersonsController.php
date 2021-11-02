<?php

namespace App\Http\Controllers\Api;

use App\Services\PersonsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class PersonsController extends BaseController
{
    private PersonsService $personsService;

    public function __construct(PersonsService $personsService)
    {
        $this->personsService = $personsService;
    }

    public function index(): JsonResponse
    {
        return response()->json($this->personsService->allPersons());
    }
}
