<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Rank;

use App\Application\Port\PersonRankHistoryReader;
use App\Application\Service\Person\Exception\PersonRankNotFound;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ShowEditActivationDateFormAction extends BaseController
{
    use RankAction;

    public function __invoke(
        string $rankId,
        PersonRankHistoryReader $reader,
    ): RedirectResponse|View {
        try {
            $rank = $reader->byId((int) $rankId) ?? throw new PersonRankNotFound();
        } catch (PersonRankNotFound) {
            return $this->redirectTo404Error();
        }

        /** @see /resources/views/ranks/show-edit-rank-activation-date.blade.php */
        return $this->view('ranks.show-edit-rank-activation-date', ['rank' => $rank]);
    }
}
