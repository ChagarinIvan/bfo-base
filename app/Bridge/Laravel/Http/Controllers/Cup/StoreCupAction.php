<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cup;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Cup\CupDto;
use App\Application\Service\Cup\AddCup;
use App\Application\Service\Cup\AddCupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class StoreCupAction extends BaseController
{
    use CupAction;

    public function __invoke(
        CupDto $dto,
        AddCupService $service,
        UserId $userId,
    ): RedirectResponse {
        $cup = $service->execute(new AddCup($dto, $userId));

        return $this->redirector->action(ShowCupAction::class, [$cup->id]);
    }
}
