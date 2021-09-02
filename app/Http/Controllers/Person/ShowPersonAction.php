<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Models\Person;
use App\Models\ProtocolLine;
use App\Services\RankService;
use App\Services\ViewActionsService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class ShowPersonAction extends AbstractPersonViewAction
{
    private RankService $rankService;

    public function __construct(ViewActionsService $viewService, RankService $rankService)
    {
        parent::__construct($viewService);
        $this->rankService = $rankService;
    }

    public function __invoke(Person $person): View
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
