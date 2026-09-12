<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Event;

use App\Application\Service\Event\ViewEvent;
use App\Application\Service\Event\ViewEventService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class ViewEventAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $eventId, ViewEventService $events): mixed
    {
        return $events->execute(new ViewEvent($eventId));
    }
}
