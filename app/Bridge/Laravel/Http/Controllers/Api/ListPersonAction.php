<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api;

use App\Application\Dto\Person\LegacySearchPersonDto;
use App\Application\Service\Person\ListLegacyPersons;
use App\Application\Service\Person\ListLegacyPersonsService;
use App\Bridge\Laravel\Http\Controllers\Action;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;

class ListPersonAction extends BaseController
{
    use Action;

    public function __invoke(
        LegacySearchPersonDto $searchDto,
        ListLegacyPersonsService $personsService,
    ): JsonResponse {
        return response()->json($personsService->execute(new ListLegacyPersons($searchDto)));
    }
}
