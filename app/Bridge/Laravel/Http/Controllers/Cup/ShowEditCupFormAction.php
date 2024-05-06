<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cup;

use App\Application\Service\Cup\Exception\CupNotFound;
use App\Application\Service\Cup\ViewCup;
use App\Application\Service\Cup\ViewCupService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ShowEditCupFormAction extends BaseController
{
    use CupAction;

    public function __invoke(
        string $cupId,
        ViewCupService $service,
    ): View|RedirectResponse {
        try {
            $cup = $service->execute(new ViewCup($cupId));
        } catch (CupNotFound) {
            return $this->redirectTo404Error();
        }

        /** @see /resources/views/cup/edit.blade.php */
        return $this->view('cup.edit', ['cup' => $cup]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
