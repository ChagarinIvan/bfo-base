<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Event;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Event\UniteEventsDto;
use App\Application\Dto\Event\ViewEventDto;
use App\Application\Service\Event\UniteEvents;
use App\Application\Service\Event\UniteEventsService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Bridge\Laravel\Http\Controllers\ResponseStatus;
use Illuminate\Routing\Controller as BaseController;

#[ResponseStatus(201)]
final class UniteEventsAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        int $competitionId,
        UniteEventsDto $input,
        UniteEventsService $service,
        UserId $userId,
    ): ViewEventDto {
        return $service->execute(new UniteEvents($competitionId, $input, $userId));
    }
}
