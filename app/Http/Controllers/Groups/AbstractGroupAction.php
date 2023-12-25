<?php

declare(strict_types=1);

namespace App\Http\Controllers\Groups;

use App\Http\Controllers\AbstractAction;
use App\Services\DistanceService;
use App\Services\GroupsService;
use App\Services\ViewActionsService;
use Illuminate\Routing\Redirector;

abstract class AbstractGroupAction extends AbstractAction
{
    public function __construct(
        protected ViewActionsService $viewActionsService,
        protected Redirector $redirector,
        protected GroupsService $groupsService,
        protected DistanceService $distanceService,
    ) {
        parent::__construct($viewActionsService, $redirector);
    }

    protected function isGroupsRoute(): bool
    {
        return true;
    }
}
