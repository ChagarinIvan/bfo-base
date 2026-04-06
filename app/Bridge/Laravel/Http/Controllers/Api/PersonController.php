<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api;

use App\Bridge\Laravel\Http\Resources\PersonCollection;
use App\Services\PersonsService;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Request;

class PersonController extends Controller
{
    public function __construct(
        private readonly PersonsService $personsService,
    ) {
    }

    public function index(Request $request): ResourceCollection
    {
        $personQuery = $this->personsService->getPersonsList(
            (string) $request->query->get('sort_by'),
            (int) $request->query->get('sort_mode'),
            (string) $request->query->get('search'),
        );

        /** @var LengthAwarePaginator $paginator */
        $paginator = $personQuery->paginate((int)$request->query->get('per_page'));
        $personIds = $paginator->pluck('id')->all();
        $ranks = $this->activePersonRankService->executeForPersons($personIds);

        return new PersonCollection($paginator->withQueryString());
    }
}
