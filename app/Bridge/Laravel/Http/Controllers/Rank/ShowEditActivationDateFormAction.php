<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Rank;

use App\Application\Service\Rank\Exception\RankNotFound;
use App\Application\Service\Rank\ViewRank;
use App\Application\Service\Rank\ViewRankService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ShowEditActivationDateFormAction extends BaseController
{
    use RankAction;

    public function __invoke(
        string $rankId,
        ViewRankService $service,
    ): View|RedirectResponse {
        try {
            $rank = $service->execute(new ViewRank($rankId));
        } catch (RankNotFound) {
            return $this->redirectTo404Error();
        }

        /** @see /resources/views/ranks/show-edit-rank-activation-date.blade.php */
        return $this->view('ranks.show-edit-rank-activation-date', ['rank' => $rank]);
    }
}
