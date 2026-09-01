<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Rank;

use App\Application\Dto\Person\ActivatePersonRankDto;
use App\Application\Service\Person\ActivatePersonRank;
use App\Application\Service\Person\ActivatePersonRankService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

final class ActivatePersonRankAction extends BaseController
{
    use RankAction;

    public function __invoke(
        string $id,
        ActivatePersonRankDto $activation,
        ActivatePersonRankService $service,
    ): RedirectResponse {
        $personId = $service->execute(new ActivatePersonRank($id, $activation));

        return $this->redirector->action(ShowPersonRanksAction::class, [$personId]);
    }
}
