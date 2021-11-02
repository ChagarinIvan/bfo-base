<?php

namespace App\Http\Controllers\Person;

use App\Http\Controllers\AbstractAction;
use App\Services\PersonsService;
use App\Services\ProtocolLineService;
use App\Services\RankService;
use App\Services\ViewActionsService;
use Illuminate\Routing\Redirector;

abstract class AbstractPersonAction extends AbstractAction
{
    protected RankService $rankService;
    protected ProtocolLineService $protocolLinesService;
    protected PersonsService $personsService;

    public function __construct(
        ViewActionsService $viewService,
        Redirector $redirector,
        RankService $rankService,
        ProtocolLineService $protocolLinesService,
        PersonsService $personsService,
    ) {
        parent::__construct($viewService, $redirector);
        $this->rankService = $rankService;
        $this->protocolLinesService = $protocolLinesService;
        $this->personsService = $personsService;
    }

    protected function isPersonsRoute(): bool
    {
        return true;
    }
}
