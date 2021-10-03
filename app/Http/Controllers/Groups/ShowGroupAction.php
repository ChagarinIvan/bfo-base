<?php

namespace App\Http\Controllers\Groups;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShowGroupAction extends AbstractGroupAction
{
    /**
     * @param int $groupId
     * @return View|RedirectResponse
     * @template resources/views/groups/show.blade.php
     */
    public function __invoke(int $groupId): View|RedirectResponse
    {
        $group = $this->groupsService->getGroup($groupId);
        if ($group === null) {
            return $this->redirectToError();
        }

        return $this->view('groups.show', [
            'group' => $group,
        ]);
    }
}
