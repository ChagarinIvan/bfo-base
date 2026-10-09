<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Person;

use App\Application\Dto\Person\SearchPersonDto;
use App\Application\Service\Person\ExportPersonRanks;
use App\Application\Service\Person\ExportPersonRanksService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Bridge\Laravel\Http\Serialization\PersonRanksCsvSerializer;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExportPersonRanksAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        SearchPersonDto $search,
        ExportPersonRanksService $service,
        PersonRanksCsvSerializer $serializer,
    ): StreamedResponse {
        return $serializer->toResponse($service->execute(new ExportPersonRanks($search)));
    }
}
