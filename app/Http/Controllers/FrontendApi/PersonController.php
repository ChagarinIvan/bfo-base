<?php

declare(strict_types=1);

namespace App\Http\Controllers\FrontendApi;

use App\Http\Resources\PersonCollection;
use App\Http\Resources\PersonResource;
use App\Models\Person;
use App\Services\PersonsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Request;

class PersonController extends Controller
{
    public function __construct(private readonly PersonsService $personsService)
    {}

    public function index(Request $request): ResourceCollection
    {
        $personQuery = $this->personsService->getPersonsList(
            (string)$request->get('sort_by'),
            (int)$request->get('sort_mode'),
            (string)$request->get('search')
        );

        return new PersonCollection($personQuery->paginate((int)$request->get('per_page'))->withQueryString());
    }

    public function store(Request $request): JsonResource
    {
        $formParams = $request->validate([
            'lastname' => 'required',
            'firstname' => 'required',
            'birthday' => 'required|date',
            'club_id' => 'required|int',
        ]);

        $person = $this->personsService->fillPerson(new Person(), $formParams);
        $person = $this->personsService->storePerson($person);

        return new PersonResource($person);
    }

    public function show(int $id): JsonResource
    {
        $person = $this->personsService->getPerson($id);
        return new PersonResource($person);
    }

    public function update(Request $request, Person $person): JsonResource
    {
        $formParams = $request->validate([
            'lastname' => 'required',
            'firstname' => 'required',
            'birthday' => 'required|date',
            'club_id' => 'int',
        ]);

        $this->personsService->updatePerson($person, $formParams);

        return new PersonResource($person);
    }

    public function destroy(Person $person): JsonResponse
    {
        $this->personsService->deletePerson($person);
        return response()->json();
    }
}
