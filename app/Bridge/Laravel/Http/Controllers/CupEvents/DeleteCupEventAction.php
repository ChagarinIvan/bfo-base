<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\CupEvents;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\CupEvent\DisableCupEvent;
use App\Application\Service\CupEvent\DisableCupEventService;
use App\Application\Service\CupEvent\Exception\CupEventNotFound;
use App\Bridge\Laravel\Http\Controllers\Cup\CupAction;
use App\Bridge\Laravel\Http\Controllers\Cup\ShowCupAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class DeleteCupEventAction extends BaseController
{
    use CupAction;

    public function __invoke(
        string $cupId,
        string $cupEventId,
        DisableCupEventService $service,
        UserId $userId,
    ): RedirectResponse {
        try {
            $service->execute(new DisableCupEvent($cupEventId, $userId));
        } catch (CupEventNotFound) {
            return $this->redirectTo404Error();
        }

        return $this->redirector->action(ShowCupAction::class, [$cupId]);
    }
}
