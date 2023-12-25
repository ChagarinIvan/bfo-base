<?php
declare(strict_types=1);

namespace App\Http\Controllers\Groups;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShowGroupsListAction extends AbstractGroupAction
{
    /**
     * @return View
     * @template resources/views/groups/index.blade.php
     */
    public function __invoke(): View|RedirectResponse
    {
        return $this->view('groups.index', [
            'groups' => $this->groupsService->getGroupsList(['distances']),
        ]);
    }
}
