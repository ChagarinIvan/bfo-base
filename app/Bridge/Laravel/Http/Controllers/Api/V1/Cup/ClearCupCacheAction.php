<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Cup;

use App\Application\Service\Cup\ClearCupCacheService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

final class ClearCupCacheAction extends BaseController
{
    use ApiAction;

    public function __invoke(ClearCupCacheService $service): Response
    {
        $service->execute();

        return response()->noContent();
    }
}
