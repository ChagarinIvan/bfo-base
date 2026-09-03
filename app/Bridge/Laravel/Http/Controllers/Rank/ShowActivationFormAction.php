<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Rank;

use App\Domain\ProtocolLine\ProtocolLineRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ShowActivationFormAction extends BaseController
{
    use RankAction;

    public function __invoke(
        string $protocolLineId,
        ProtocolLineRepository $protocolLines,
    ): RedirectResponse|View {
        $rank = $protocolLines->byId((int) $protocolLineId);
        if ($rank === null) {
            return $this->redirectTo404Error();
        }

        /** @see /resources/views/ranks/show-person-rank-activation.blade.php */
        return $this->view('ranks.show-person-rank-activation', ['rank' => $rank]);
    }
}
