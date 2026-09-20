<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Cup;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\CupEvent\CupEventDto;
use App\Application\Dto\CupEvent\ViewCupEventDto;
use App\Application\Service\CupEvent\UpdateCupEvent;
use App\Application\Service\CupEvent\UpdateCupEventService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class UpdateCupEventAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $cupEventId, CupEventDto $cupEvent, UpdateCupEventService $service, UserId $userId): ViewCupEventDto
    {
        return $service->execute(new UpdateCupEvent($cupEventId, $cupEvent, $userId));
    }
}
