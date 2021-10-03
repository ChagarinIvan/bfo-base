<?php

declare(strict_types=1);

namespace App\Http\Controllers\Groups;

use App\Http\Controllers\AbstractAction;
use App\Services\BackUrlService;
use App\Services\GroupsService;
use App\Services\ViewActionsService;
use Illuminate\Routing\Redirector;

abstract class AbstractGroupAction extends AbstractAction
{
    protected GroupsService $groupsService;

    public function __construct(
        ViewActionsService $viewActionsService,
        Redirector $redirector,
        BackUrlService $backUrlService,
        GroupsService $groupsService,
    ) {
        parent::__construct($viewActionsService, $redirector, $backUrlService);
        $this->groupsService = $groupsService;
    }

    protected function isGroupsRoute(): bool
    {
        return true;
    }
}
