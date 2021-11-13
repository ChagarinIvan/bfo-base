<?php

namespace App\Http\Controllers\Rank;

use App\Http\Controllers\AbstractAction;
use App\Services\ParserService;
use App\Services\PersonsIdentService;
use App\Services\PersonsService;
use App\Services\ProtocolLineService;
use App\Services\RankService;
use App\Services\ViewActionsService;
use Illuminate\Routing\Redirector;

class AbstractRankAction extends AbstractAction
{
    protected RankService $rankService;
    protected ProtocolLineService $protocolLinesService;
    protected ParserService $parserService;
    protected PersonsIdentService $personsIdentService;
    protected PersonsService $personsService;

    public function __construct(
        ViewActionsService $viewService,
        Redirector $redirector,
        RankService $rankService,
        ProtocolLineService $protocolLinesService,
        ParserService $parserService,
        PersonsIdentService $identService,
        PersonsService $personsService,
    ) {
        parent::__construct($viewService, $redirector);
        $this->rankService = $rankService;
        $this->protocolLinesService = $protocolLinesService;
        $this->parserService = $parserService;
        $this->personsIdentService = $identService;
        $this->personsService = $personsService;
    }

    protected function isRanksRoute(): bool
    {
        return true;
    }
}
