<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Rank;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\UpdatePersonRankActivationDateDto;
use App\Application\Service\Person\UpdatePersonRankActivationDate;
use App\Application\Service\Person\UpdatePersonRankActivationDateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

final class UpdateRankActivationDateAction extends BaseController
{
    use RankAction;

    public function __invoke(
        string $protocolLineId,
        UpdatePersonRankActivationDateDto $activation,
        UpdatePersonRankActivationDateService $service,
        UserId $userId,
    ): RedirectResponse {
        $personId = $service->execute(new UpdatePersonRankActivationDate($protocolLineId, $activation, $userId));

        return $this->redirector->action(ShowPersonRanksAction::class, [$personId]);
    }
}
