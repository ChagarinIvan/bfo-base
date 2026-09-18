<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Cup;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Cup\CupDto;
use App\Application\Dto\Cup\ViewCupDto;
use App\Application\Service\Cup\AddCup;
use App\Application\Service\Cup\AddCupService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Bridge\Laravel\Http\Controllers\ResponseStatus;
use Illuminate\Routing\Controller as BaseController;

#[ResponseStatus(201)]
final class CreateCupAction extends BaseController
{
    use ApiAction;

    public function __invoke(CupDto $info, AddCupService $service, UserId $userId): ViewCupDto
    {
        return $service->execute(new AddCup($info, $userId));
    }
}
