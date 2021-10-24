<?php

namespace App\Http\Controllers\Groups;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShowUnitGroupsAction extends AbstractGroupAction
{
    public function __invoke(int $groupId): View|RedirectResponse
    {
        $group = $this->groupsService->getGroup($groupId);

        return $this->view('groups.unit', [
            'unitedGroup' => $group,
            'groups' => $this->groupsService->getAllGroupsWithout([$groupId]),
        ]);
    }
}
