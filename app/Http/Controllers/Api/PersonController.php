<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\PersonCollection;
use App\Services\PersonsService;
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
}
