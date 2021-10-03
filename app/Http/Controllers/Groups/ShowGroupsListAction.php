<?php

namespace App\Http\Controllers\Groups;

use Illuminate\View\View;

class ShowGroupsListAction extends AbstractGroupAction
{
    /**
     * @return View
     * @template resources/views/groups/index.blade.php
     */
    public function __invoke(): View
    {
        return $this->view('groups.index', [
            'groups' => $this->groupsService->getGroupsList(['distances']),
        ]);
    }
}
