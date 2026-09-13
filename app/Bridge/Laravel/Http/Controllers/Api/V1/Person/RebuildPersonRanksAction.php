<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Person;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\RebuildPersonRanks;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

final class RebuildPersonRanksAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        string $personId,
        RebuildPersonRanksService $service,
        UserId $userId,
    ): Response {
        $service->execute(new RebuildPersonRanks((int) $personId, $userId));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
