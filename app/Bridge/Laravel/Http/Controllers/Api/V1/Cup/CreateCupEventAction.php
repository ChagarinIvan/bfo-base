<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Cup;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\CupEvent\CreateCupEventDto;
use App\Application\Dto\CupEvent\ViewCupEventDto;
use App\Application\Service\CupEvent\AddCupEvent;
use App\Application\Service\CupEvent\AddCupEventService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Bridge\Laravel\Http\Controllers\ResponseStatus;
use Illuminate\Routing\Controller as BaseController;

#[ResponseStatus(201)]
final class CreateCupEventAction extends BaseController
{
    use ApiAction;

    public function __invoke(CreateCupEventDto $cupEvent, AddCupEventService $service, UserId $userId): ViewCupEventDto
    {
        return $service->execute(new AddCupEvent($cupEvent, $userId));
    }
}
