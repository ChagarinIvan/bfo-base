<?php

namespace App\Http\Controllers\Groups;

use Illuminate\Http\RedirectResponse;

class DeleteGroupAction extends AbstractGroupAction
{
    public function __invoke(int $groupId): RedirectResponse
    {
        $group = $this->groupsService->getGroup($groupId);
        $this->groupsService->deleteGroup($group);
        return $this->redirector->back();
    }
}
