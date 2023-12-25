<?php
declare(strict_types=1);

namespace App\Http\Controllers\Groups;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

class ShowGroupAction extends AbstractGroupAction
{
    /**
     * @see resources/views/groups/show.blade.php
     */
    public function __invoke(int $groupId): View|RedirectResponse
    {
        try {
            $group = $this->groupsService->getGroup($groupId);
        } catch (RuntimeException) {
            return $this->redirectTo404Error();
        }

        return $this->view('groups.show', [
            'group' => $group,
        ]);
    }
}
