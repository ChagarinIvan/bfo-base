<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cup;

use App\Application\Dto\Cup\CupSearchDto;
use App\Application\Service\Cup\ListCup;
use App\Application\Service\Cup\ListCupService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;

class ShowCupsListAction extends BaseController
{
    use CupAction;

    public function __invoke(
        CupSearchDto $search,
        ListCupService $service,
    ): View {
        $cups = $service->execute(new ListCup($search));

        /** @see /resources/views/cup/index.blade.php */
        return $this->view('cup.index', [
            'selectedYear' => $search->year,
            'cups' => $cups,
        ]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
