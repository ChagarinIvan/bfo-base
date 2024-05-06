<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cup;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Cup\CupDto;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Application\Service\Cup\UpdateCup;
use App\Application\Service\Cup\UpdateCupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class UpdateCupAction extends BaseController
{
    use CupAction;

    public function __invoke(
        string $cupId,
        CupDto $dto,
        UpdateCupService $service,
        UserId $userId,
    ): RedirectResponse {
        try {
            $cup = $service->execute(new UpdateCup($cupId, $dto, $userId));
        } catch (CupNotFound) {
            return $this->redirectTo404Error();
        }

        return $this->redirector->action(ShowCupAction::class, [$cup->id]);
    }
}
