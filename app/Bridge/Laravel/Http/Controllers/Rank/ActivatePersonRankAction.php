<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Rank;

use App\Application\Dto\Rank\ActivationDto;
use App\Application\Service\Rank\ActivateRank;
use App\Application\Service\Rank\ActivateRankService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

final class ActivatePersonRankAction extends BaseController
{
    use RankAction;

    public function __invoke(
        string $id,
        ActivationDto $activation,
        ActivateRankService $service,
    ): RedirectResponse {
        $rank = $service->execute(new ActivateRank($id, $activation));

        return $this->redirector->action(ShowPersonRanksAction::class, [$rank->personId]);
    }
}
