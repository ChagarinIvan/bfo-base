<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Groups;

use Illuminate\Contracts\View\View;

class ShowGroupsListAction extends AbstractGroupAction
{
    /**
     * @see resources/views/groups/index.blade.php
     */
    public function __invoke(): View
    {
        return $this->view('groups.index', [
            'groups' => $this->groupsService->getGroupsList(['distances']),
        ]);
    }
}
