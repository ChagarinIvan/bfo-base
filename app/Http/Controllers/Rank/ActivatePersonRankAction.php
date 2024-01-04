<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rank;

use App\Models\Person;
use App\Models\Rank;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ActivatePersonRankAction extends AbstractRankAction
{
    public function __invoke(Person $person, Rank $rank, Request $request): RedirectResponse
    {
        if ($rank->active) {
            return $this->redirector->action(ShowPersonRanksAction::class, [$person]);
        }

        $formParams = $request->validate([
            'start_date' => 'required|date',
        ]);

        dump($formParams);
        $this->rankService->activateRank($rank, Carbon::createFromFormat('Y-m-d', $formParams['start_date']));

        return $this->redirector->action(ShowPersonRanksAction::class, [$person]);
    }
}
