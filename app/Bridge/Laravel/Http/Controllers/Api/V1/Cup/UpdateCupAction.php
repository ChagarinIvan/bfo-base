<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Cup;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Cup\CupDto;
use App\Application\Dto\Cup\ViewCupDto;
use App\Application\Service\Cup\UpdateCup;
use App\Application\Service\Cup\UpdateCupService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class UpdateCupAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        string $cupId,
        CupDto $info,
        UpdateCupService $service,
        UserId $userId,
    ): ViewCupDto {
        return $service->execute(new UpdateCup($cupId, $info, $userId));
    }
}
