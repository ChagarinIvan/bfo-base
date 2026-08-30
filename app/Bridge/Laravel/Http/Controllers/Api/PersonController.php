<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api;

use App\Bridge\Laravel\Http\Resources\PersonCollection;
use App\Services\PersonsService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;

class PersonController extends Controller
{
    public function __construct(private readonly PersonsService $personsService)
    {
    }

    public function index(Request $request): ResourceCollection
    {
        $personQuery = $this->personsService->getPersonsList(
            (string) $request->input('sort_by'),
            (int) $request->input('sort_mode'),
            (string) $request->input('search'),
        );

        /** @var LengthAwarePaginator $paginator */
        $paginator = $personQuery->paginate((int)$request->input('per_page'));

        return new PersonCollection($paginator->withQueryString());
    }
}
