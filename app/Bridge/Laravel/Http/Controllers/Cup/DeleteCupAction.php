<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cup;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Cup\DisableCup;
use App\Application\Service\Cup\DisableCupService;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Application\Service\Cup\ViewCup;
use App\Application\Service\Cup\ViewCupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class DeleteCupAction extends BaseController
{
    use CupAction;

    public function __invoke(
        string $cupId,
        ViewCupService $viewService,
        DisableCupService $disableService,
        UserId $userId,
    ): RedirectResponse {
        try {
            $cup = $viewService->execute(new ViewCup($cupId));
            $disableService->execute(new DisableCup($cupId, $userId));
        } catch (CupNotFound) {
            return $this->redirectTo404Error();
        }

        return $this->redirector->action(ShowCupsListAction::class, ['year' => $cup->year]);
    }
}
