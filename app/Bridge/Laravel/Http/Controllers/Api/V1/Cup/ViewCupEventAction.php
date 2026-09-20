<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Cup;

use App\Application\Dto\CupEvent\ViewCupEventDto;
use App\Application\Service\CupEvent\ViewCupEvent;
use App\Application\Service\CupEvent\ViewCupEventService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class ViewCupEventAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $cupEventId, ViewCupEventService $service): ViewCupEventDto
    {
        return $service->execute(new ViewCupEvent($cupEventId));
    }
}
