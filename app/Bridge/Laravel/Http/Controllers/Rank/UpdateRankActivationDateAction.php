<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Rank;

use App\Application\Dto\Person\UpdatePersonRankActivationDateDto;
use App\Application\Service\Person\UpdatePersonRankActivationDate;
use App\Application\Service\Person\UpdatePersonRankActivationDateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

final class UpdateRankActivationDateAction extends BaseController
{
    use RankAction;

    public function __invoke(
        string $id,
        UpdatePersonRankActivationDateDto $activation,
        UpdatePersonRankActivationDateService $service,
    ): RedirectResponse {
        $personId = $service->execute(new UpdatePersonRankActivationDate($id, $activation));

        return $this->redirector->action(ShowPersonRanksAction::class, [$personId]);
    }
}
