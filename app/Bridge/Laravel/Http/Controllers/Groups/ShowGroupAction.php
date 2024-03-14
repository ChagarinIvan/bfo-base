<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Groups;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

class ShowGroupAction extends AbstractGroupAction
{
    /**
     * @see resources/views/groups/show.blade.php
     */
    public function __invoke(string $groupId): View|RedirectResponse
    {
        try {
            $group = $this->groupsService->getGroup((int) $groupId);
        } catch (RuntimeException) {
            return $this->redirectTo404Error();
        }

        return $this->view('groups.show', [
            'group' => $group,
        ]);
    }
}
