<?php

namespace App\Http\Controllers\FrontendApi;

use App\Models\Person;
use App\Services\PersonsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class DeletePersonAction extends Controller
{
    public function __construct(
        private readonly PersonsService $personsService
    ) {}

    public function __invoke(Person $person): JsonResponse
    {
        $this->personsService->deletePerson($person);
        return response()->json();
    }
}
