<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Rank;

use App\Domain\Person\Person;
use App\Models\Rank;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ActivatePersonRankAction extends AbstractRankAction
{
    public function __invoke(Person $person, Rank $rank, Request $request): RedirectResponse
    {
        if ($rank->activated_date) {
            return $this->redirector->action(ShowPersonRanksAction::class, [$person]);
        }

        $formParams = $request->validate([
            'date' => 'required|date',
        ]);

        $this->rankService->activateRank($rank, Carbon::createFromFormat('Y-m-d', $formParams['date']));

        return $this->redirector->action(ShowPersonRanksAction::class, [$person]);
    }
}
