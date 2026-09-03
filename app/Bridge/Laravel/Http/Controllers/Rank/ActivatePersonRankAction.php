<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Rank;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\ActivatePersonRankDto;
use App\Application\Service\Person\ActivatePersonRank;
use App\Application\Service\Person\ActivatePersonRankService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

final class ActivatePersonRankAction extends BaseController
{
    use RankAction;

    public function __invoke(
        string $protocolLineId,
        ActivatePersonRankDto $activation,
        ActivatePersonRankService $service,
        UserId $userId,
    ): RedirectResponse {
        $personId = $service->execute(new ActivatePersonRank($protocolLineId, $activation, $userId));

        return $this->redirector->action(ShowPersonRanksAction::class, [$personId]);
    }
}
