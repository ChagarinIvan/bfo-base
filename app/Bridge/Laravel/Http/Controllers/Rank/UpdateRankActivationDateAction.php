<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Rank;

use App\Application\Dto\Rank\ActivationDto;
use App\Application\Dto\Rank\UpdateActivationDto;
use App\Application\Service\Rank\ActivateRank;
use App\Application\Service\Rank\ActivateRankService;
use App\Application\Service\Rank\UpdateRankActivationDate;
use App\Application\Service\Rank\UpdateRankActivationDateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

final class UpdateRankActivationDateAction extends BaseController
{
    use RankAction;

    public function __invoke(
        string $id,
        UpdateActivationDto $activation,
        UpdateRankActivationDateService $service,
    ): RedirectResponse {
        $rank = $service->execute(new UpdateRankActivationDate($id, $activation));

        return $this->redirector->action(ShowPersonRanksAction::class, [$rank->personId]);
    }
}
