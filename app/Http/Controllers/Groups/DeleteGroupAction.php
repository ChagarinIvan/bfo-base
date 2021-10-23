<?php

namespace App\Http\Controllers\Groups;

use Illuminate\Http\RedirectResponse;

class DeleteGroupAction extends AbstractGroupAction
{
    public function __invoke(int $groupId): RedirectResponse
    {
        $group = $this->groupsService->getGroup($groupId);
        if ($group === null) {
            return $this->redirectToError();
        }
        $this->groupsService->deleteGroup($group);
        return $this->redirector->back();
    }
}
