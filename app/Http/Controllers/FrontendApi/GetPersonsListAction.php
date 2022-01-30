<?php

declare(strict_types=1);

namespace App\Http\Controllers\FrontendApi;

use App\Services\PersonsService;
use App\Services\RankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Request;

class GetPersonsListAction extends Controller
{
    public function __construct(
        private readonly PersonsService $personsService,
        private readonly RankService $rankService,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $personsList = $this->personsService->getPersonsList(
            (int)$request->get('limit'),
            (int)$request->get('offset'),
            (string)$request->get('sort-by'),
            (int)$request->get('sort-mode'),
            (string)$request->get('search'),
        );
        $actualRanks = $this->rankService->getActualRanks($personsList->entities->pluck('id'));
        $personsList->entities = $this->formatPersons($personsList->entities, $actualRanks);

        return response()->json($personsList->toArray());
    }

    private function formatPersons(Collection $persons, Collection $ranks): Collection
    {
        return $persons->map(function(\stdClass $person) use ($ranks) {
            $person->rank = $ranks->has($person->id) ? $ranks->get($person->id)->rank : '';
            return $person;
        });
    }
}
