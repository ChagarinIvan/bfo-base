<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Groups;

use Illuminate\Http\RedirectResponse;

class DeleteGroupAction extends AbstractGroupAction
{
    public function __invoke(string $groupId): RedirectResponse
    {
        $group = $this->groupsService->getGroup((int) $groupId);
        $this->groupsService->deleteGroup($group);
        return $this->redirector->back();
    }
}
