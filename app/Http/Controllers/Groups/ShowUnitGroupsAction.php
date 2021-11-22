<?php

namespace App\Http\Controllers\Groups;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShowUnitGroupsAction extends AbstractGroupAction
{
    public function __invoke(int $groupId): View|RedirectResponse
    {
        try {
            $group = $this->groupsService->getGroup($groupId);
        } catch (\RuntimeException) {
            $this->redirectTo404Error();
        }

        return $this->view('groups.unit', [
            'unitedGroup' => $group,
            'groups' => $this->groupsService->getAllGroupsWithout([$groupId]),
        ]);
    }
}
