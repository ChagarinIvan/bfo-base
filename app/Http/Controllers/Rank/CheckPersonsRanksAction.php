<?php

namespace App\Http\Controllers\Rank;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CheckPersonsRanksAction extends AbstractRankAction
{
    public function __invoke(Request $request): View
    {
        $list = $request->file('list');
        $list = $list->getContent();
        $list = $this->parserService->parserRankList($list);
        $list = $this->personsIdentService->preparedLines($list);
        $personsList = $this->personsIdentService->identLines($list);
        $persons = $this->personsService->getPersons($personsList->values())->keyBy('id');
        $ranks = $this->rankService->getActualRanks($personsList->values());

        return $this->view(
            'ranks.check-list',
            compact('list', 'ranks', 'personsList', 'persons')
        );
    }
}
