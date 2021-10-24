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
    protected GroupsService $groupsService;
    protected DistanceService $distanceService;

    public function __construct(
        ViewActionsService $viewActionsService,
        Redirector $redirector,
        GroupsService $groupsService,
        DistanceService $distanceService,
    ) {
        parent::__construct($viewActionsService, $redirector);
        $this->groupsService = $groupsService;
        $this->distanceService = $distanceService;
    }

    protected function isGroupsRoute(): bool
    {
        return true;
    }
}
