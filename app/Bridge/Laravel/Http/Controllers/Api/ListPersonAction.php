<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api;

use App\Application\Dto\Person\PersonSearchDto;
use App\Application\Service\Person\ListPersons;
use App\Application\Service\Person\ListPersonsService;
use App\Bridge\Laravel\Http\Controllers\Action;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;

class ListPersonAction extends BaseController
{
    use Action;

    public function __invoke(
        PersonSearchDto $searchDto,
        ListPersonsService $personsService,
    ): JsonResponse {
        return response()->json($personsService->execute(new ListPersons($searchDto)));
    }
}
