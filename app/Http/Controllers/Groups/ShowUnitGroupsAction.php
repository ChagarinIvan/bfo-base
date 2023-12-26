<?php

declare(strict_types=1);

namespace App\Http\Controllers\Groups;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

class ShowUnitGroupsAction extends AbstractGroupAction
{
    public function __invoke(string $groupId): View|RedirectResponse
    {
        try {
            $group = $this->groupsService->getGroup((int) $groupId);
        } catch (RuntimeException) {
            return $this->redirectTo404Error();
        }

        return $this->view('groups.unit', [
            'unitedGroup' => $group,
            'groups' => $this->groupsService->getAllGroupsWithout([$groupId]),
        ]);
    }
}
