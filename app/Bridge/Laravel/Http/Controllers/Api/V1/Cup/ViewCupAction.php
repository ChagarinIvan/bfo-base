<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Cup;

use App\Application\Dto\Cup\ViewCupDto;
use App\Application\Service\Cup\ViewCup;
use App\Application\Service\Cup\ViewCupService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class ViewCupAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $cupId, ViewCupService $service): ViewCupDto
    {
        return $service->execute(new ViewCup($cupId));
    }
}
