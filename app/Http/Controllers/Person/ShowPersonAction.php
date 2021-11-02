<?php

namespace App\Http\Controllers\Person;

use App\Models\Person;
use App\Models\ProtocolLine;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;

class ShowPersonAction extends AbstractPersonAction
{
    public function __invoke(Person $person): View|RedirectResponse
    {
        /** fn features from php 7.4 */
        $groupedProtocolLines = $person->protocolLines->groupBy(fn (ProtocolLine $line) => $line->distance->event->date->format('Y'));
        $groupedProtocolLines->transform(function (Collection $protocolLines) {
            /** fn features from php 7.4 */
            return $protocolLines->sortByDesc(fn(ProtocolLine $line) => $line->distance->event->date);
        });
        $groupedProtocolLines = $groupedProtocolLines->sortKeysDesc();

        return $this->view('persons.show', [
            'person' => $person,
            'groupedProtocolLines' => $groupedProtocolLines,
            'rank' => $this->rankService->getActualRank($person->id),
        ]);
    }
}
