<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Rank;

use App\Bridge\Laravel\Http\Controllers\AbstractAction;
use App\Services\ParserService;
use App\Services\PersonsIdentService;
use App\Services\PersonsService;
use App\Services\ProtocolLineService;
use App\Services\RankService;
use App\Services\ViewActionsService;
use Illuminate\Routing\Redirector;

class AbstractRankAction extends AbstractAction
{
    public function __construct(
        protected ViewActionsService $viewService,
        protected Redirector $redirector,
        protected RankService $rankService,
        protected ProtocolLineService $protocolLinesService,
        protected ParserService $parserService,
        protected PersonsIdentService $identService,
        protected PersonsService $personsService,
    ) {
        parent::__construct($viewService, $redirector);
    }

    protected function isRanksRoute(): bool
    {
        return true;
    }
}
